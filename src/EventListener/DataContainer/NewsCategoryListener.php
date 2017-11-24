<?php

/*
 * News Categories Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\EventListener\DataContainer;

use Codefog\NewsCategoriesBundle\MultilingualHelper;
use Codefog\NewsCategoriesBundle\PermissionChecker;
use Contao\Backend;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\DataContainer;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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
     * @var SessionInterface
     */
    private $session;

    /**
     * NewsCategoryListener constructor.
     *
     * @param Connection        $db
     * @param PermissionChecker $permissionChecker
     * @param SessionInterface  $session
     */
    public function __construct(Connection $db, PermissionChecker $permissionChecker, SessionInterface $session)
    {
        $this->db = $db;
        $this->permissionChecker = $permissionChecker;
        $this->session = $session;
    }

    /**
     * On data container load.
     *
     * @param DataContainer $dc
     */
    public function onLoadCallback(DataContainer $dc)
    {
        if (!$this->permissionChecker->canUserManageCategories()) {
            throw new AccessDeniedException('User has no permissions to manage news categories');
        }

        // Limit the allowed roots for the user
        if (null !== ($roots = $this->permissionChecker->getUserAllowedRoots())) {
            $GLOBALS['TL_DCA'][$dc->table]['list']['sorting']['root'] = $roots;

            /** @var Input $input */
            $input = $this->framework->getAdapter(Input::class);

            // Check current action
            switch ($action = $input->get('act')) {
                case 'edit':
                    $categoryId = (int) $input->get('id');

                    // Dynamically add the record to the user profile
                    if (!$this->permissionChecker->isUserAllowedNewsCategory($categoryId)) {
                        /** @var \Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface $sessionBag */
                        $sessionBag = $this->session->getbag('contao_backend');

                        $newRecords = $sessionBag->get('new_records');
                        $newRecords = is_array($newRecords[$dc->table]) ? array_map('intval', $newRecords[$dc->table]) : [];

                        if (in_array($categoryId, $newRecords, true)) {
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
                    $session = $this->session->all();
                    $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $roots);
                    $this->session->replace($session);
                    break;
            }
        }
    }

    /**
     * On paste button callback.
     *
     * @param DataContainer $dc
     * @param array         $row
     * @param string        $table
     * @param bool          $cr
     * @param array|null    $clipboard
     *
     * @return string
     */
    public function onPasteButtonCallback(DataContainer $dc, array $row, $table, $cr, array $clipboard = null)
    {
        $disablePA = false;
        $disablePI = false;

        // Disable all buttons if there is a circular reference
        if (null !== $clipboard && (
                ('cut' === $clipboard['mode'] && ($cr || (int) $clipboard['id'] === (int) $row['id']))
                || ('cutAll' === $clipboard['mode'] && ($cr || in_array((int) $row['id'], array_map('intval', $clipboard['id']), true)))
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
     * @param array         $row
     * @param string        $label
     * @param DataContainer $dc
     * @param string        $attributes
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
     * @param string        $value
     * @param DataContainer $dc
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public function onGenerateAlias($value, DataContainer $dc)
    {
        $autoAlias = false;

        // Generate alias if there is none
        if (!$value) {
            $autoAlias = true;
            $value = StringUtil::generateAlias($dc->activeRecord->frontendTitle ?: $dc->activeRecord->title);
        }

        if (MultilingualHelper::isActive() && $dc instanceof Driver) {
            $exists = $this->db->fetchColumn(
                "SELECT id FROM {$dc->table} WHERE alias=? AND id!=? AND {$dc->getLanguageColumn()}=?",
                [$value, $dc->activeRecord->id, $dc->getCurrentLanguage()]
            );
        } else {
            $exists = $this->db->fetchColumn(
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
     * @param string     $type
     * @param bool       $disabled
     * @param string     $table
     * @param array      $row
     * @param array|null $clipboard
     *
     * @return string
     */
    private function generatePasteImage($type, $disabled, $table, array $row, array $clipboard = null)
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

        $url = sprintf('act=%s&amp;mode=%s&amp;pid=%s', $clipboard['mode'], ('pasteafter' === $type ? 1 : 2), $row['id']);

        // Add the ID to the URL if the clipboard does not contain any
        if (!is_array($clipboard['id'])) {
            $url .= '&amp;id='.$clipboard['id'];
        }

        return sprintf(
            '<a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a> ',
            $backendAdapter->addToUrl($url),
            specialchars(sprintf($GLOBALS['TL_LANG'][$table][$type][1], $row['id'])),
            $imageAdapter->getHtml($type.'.svg', sprintf($GLOBALS['TL_LANG'][$table][$type][1], $row['id']))
        );
    }
}
