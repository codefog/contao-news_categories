<?php

declare(strict_types=1);

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\EventListener\DataContainer;

use Codefog\NewsCategoriesBundle\MultilingualHelper;
use Codefog\NewsCategoriesBundle\PermissionChecker;
use Contao\Backend;
use Contao\Config;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\CoreBundle\Slug\Slug;
use Contao\DataContainer;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Terminal42\DcMultilingualBundle\Driver;

class NewsCategoryListener implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    /**
     * @var Connection
     */
    private $db;

    /**
     * @var PermissionChecker
     */
    private $permissionChecker;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var Slug
     */
    private $slug;

    public function __construct(Connection $db, PermissionChecker $permissionChecker, RequestStack $requestStack, Slug|null $slug = null)
    {
        $this->db = $db;
        $this->permissionChecker = $permissionChecker;
        $this->requestStack = $requestStack;
        $this->slug = $slug;
    }

    /**
     * On data container load.
     */
    public function onLoadCallback(DataContainer $dc): void
    {
        if (!$this->permissionChecker->canUserManageCategories() && !$this->permissionChecker->canUserAssignCategories()) {
            throw new AccessDeniedException('User has no permissions to manage news categories');
        }

        // Disable some features if user is not allowed to manage categories
        if (!$this->permissionChecker->canUserManageCategories()) {
            $GLOBALS['TL_DCA'][$dc->table]['config']['closed'] = true;
            $GLOBALS['TL_DCA'][$dc->table]['config']['notEditable'] = true;
            $GLOBALS['TL_DCA'][$dc->table]['config']['notDeletable'] = true;
            $GLOBALS['TL_DCA'][$dc->table]['config']['notCopyable'] = true;
            $GLOBALS['TL_DCA'][$dc->table]['config']['notSortable'] = true;

            unset($GLOBALS['TL_DCA'][$dc->table]['list']['global_operations']['all']);

            $GLOBALS['TL_DCA'][$dc->table]['list']['operations'] = array_intersect_key(
                $GLOBALS['TL_DCA'][$dc->table]['list']['operations'],
                array_flip(['show'])
            );
        }

        /** @var Input $input */
        $input = $this->framework->getAdapter(Input::class);

        // Get the news categories root set previously in session (see #137)
        if (isset($_SESSION['NEWS_CATEGORIES_ROOT']) && $input->get('picker')) {
            $GLOBALS['TL_DCA'][$dc->table]['list']['sorting']['root'] = $_SESSION['NEWS_CATEGORIES_ROOT'];
            unset($_SESSION['NEWS_CATEGORIES_ROOT']);
        } else {
            unset($GLOBALS['TL_DCA'][$dc->table]['list']['sorting']['root']);
        }

        // Limit the allowed roots for the user
        if (null !== ($roots = $this->permissionChecker->getUserAllowedRoots())) {
            if (isset($GLOBALS['TL_DCA'][$dc->table]['list']['sorting']['root']) && \is_array($GLOBALS['TL_DCA'][$dc->table]['list']['sorting']['root'])) {
                $roots = array_intersect($GLOBALS['TL_DCA'][$dc->table]['list']['sorting']['root'], $roots);
                $roots = 0 === \count($roots) ? [0] : $roots;
            }

            // Unset the root to avoid error with filters (see #157)
            if (0 === \count($roots)) {
                unset($GLOBALS['TL_DCA'][$dc->table]['list']['sorting']['root']);
            } else {
                $GLOBALS['TL_DCA'][$dc->table]['list']['sorting']['root'] = $roots;
            }

            // Check current action
            switch ($action = $input->get('act')) {
                case 'edit':
                    $categoryId = (int) $input->get('id');

                    // Dynamically add the record to the user profile
                    if (!$this->permissionChecker->isUserAllowedNewsCategory($categoryId)) {
                        /** @var AttributeBagInterface $sessionBag */
                        $sessionBag = $this->requestStack->getSession()->getbag('contao_backend');

                        $newRecords = $sessionBag->get('new_records');
                        $newRecords = \is_array($newRecords[$dc->table]) ? array_map('intval', $newRecords[$dc->table]) : [];

                        if (\in_array($categoryId, $newRecords, true)) {
                            $this->permissionChecker->addCategoryToAllowedRoots($categoryId);
                        }
                    }
                    // no break;

                case 'copy':
                case 'delete':
                case 'show':
                    $categoryId = (int) $input->get('id');

                    if (!$this->permissionChecker->isUserAllowedNewsCategory($categoryId)) {
                        throw new AccessDeniedException(sprintf('Not enough permissions to %s news category ID %s.', $action, $categoryId));
                    }
                    break;
                case 'editAll':
                case 'deleteAll':
                case 'overrideAll':
                    $session = $this->requestStack->getSession()->all();
                    $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $roots);
                    $this->requestStack->getSession()->replace($session);
                    break;
            }
        }
    }

    /**
     * On paste button callback.
     *
     * @param string $table
     * @param bool   $cr
     *
     * @return string
     */
    public function onPasteButtonCallback(DataContainer $dc, array $row, $table, $cr, array|null $clipboard = null)
    {
        $disablePA = false;
        $disablePI = false;

        // Disable all buttons if there is a circular reference
        if (
            null !== $clipboard && (
                ('cut' === $clipboard['mode'] && ($cr || (int) $clipboard['id'] === (int) $row['id']))
                || ('cutAll' === $clipboard['mode'] && ($cr || \in_array((int) $row['id'], array_map('intval', $clipboard['id']), true)))
            )
        ) {
            $disablePA = true;
            $disablePI = true;
        }

        $return = '';

        if ($row['id'] > 0) {
            $return = $this->generatePasteImage('pasteafter', $disablePA, $table, $row, $clipboard);
        }

        return $return.$this->generatePasteImage('pasteinto', $disablePI, $table, $row, $clipboard);
    }

    /**
     * On label callback.
     *
     * @param string $label
     * @param string $attributes
     *
     * @return string
     */
    public function onLabelCallback(array $row, $label, DataContainer $dc, $attributes)
    {
        /** @var Image $imageAdapter */
        $imageAdapter = $this->framework->getAdapter(Image::class);

        // Align the icon with the text
        if (false !== stripos($attributes, 'style="')) {
            $attributes = str_replace('style="', 'style="vertical-align:text-top;', $attributes);
        } else {
            $attributes .= trim($attributes.' style="vertical-align:text-top;"');
        }

        return $imageAdapter->getHtml('iconPLAIN.svg', '', $attributes).' '.$label;
    }

    /**
     * On generate the category alias.
     *
     * @param string $value
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function onGenerateAlias($value, DataContainer $dc)
    {
        $autoAlias = false;

        // Generate alias if there is none
        if (!$value) {
            $autoAlias = true;
            $title = $dc->activeRecord->frontendTitle ?: $dc->activeRecord->title;

            if (null !== $this->slug) {
                $slugOptions = [];

                if (!empty($validChars = Config::get('news_categorySlugSetting'))) {
                    $slugOptions['validChars'] = $validChars;
                }

                if (MultilingualHelper::isActive() && $dc instanceof Driver) {
                    $slugOptions['locale'] = $dc->getCurrentLanguage();
                }

                $value = $this->slug->generate($title, $slugOptions);
            } else {
                $value = StringUtil::generateAlias($title);
            }
        }

        if (MultilingualHelper::isActive() && $dc instanceof Driver) {
            $exists = $this->db->fetchOne(
                "SELECT id FROM {$dc->table} WHERE alias=? AND id!=? AND {$dc->getLanguageColumn()}=?",
                [$value, $dc->activeRecord->id, $dc->getCurrentLanguage()]
            );
        } else {
            $exists = $this->db->fetchOne(
                "SELECT id FROM {$dc->table} WHERE alias=? AND id!=?",
                [$value, $dc->activeRecord->id]
            );
        }

        // Check whether the category alias exists
        if ($exists) {
            if ($autoAlias) {
                $value .= '-'.$dc->activeRecord->id;
            } else {
                throw new \RuntimeException(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $value));
            }
        }

        return $value;
    }

    /**
     * Generate the paste image.
     *
     * @param string $type
     * @param bool   $disabled
     * @param string $table
     *
     * @return string
     */
    private function generatePasteImage($type, $disabled, $table, array $row, array|null $clipboard = null)
    {
        /**
         * @var Backend
         * @var Image   $imageAdapter
         */
        $backendAdapter = $this->framework->getAdapter(Backend::class);
        $imageAdapter = $this->framework->getAdapter(Image::class);

        if ($disabled) {
            return $imageAdapter->getHtml($type.'_.svg').' ';
        }

        $url = sprintf('act=%s&amp;mode=%s&amp;pid=%s', $clipboard['mode'], 'pasteafter' === $type ? 1 : 2, $row['id']);

        // Add the ID to the URL if the clipboard does not contain any
        if (!\is_array($clipboard['id'])) {
            $url .= '&amp;id='.$clipboard['id'];
        }

        return sprintf(
            '<a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a> ',
            $backendAdapter->addToUrl($url),
            StringUtil::specialchars(sprintf($GLOBALS['TL_LANG'][$table][$type][1], $row['id'])),
            $imageAdapter->getHtml($type.'.svg', sprintf($GLOBALS['TL_LANG'][$table][$type][1], $row['id']))
        );
    }
}
