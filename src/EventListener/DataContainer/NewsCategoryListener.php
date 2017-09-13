<?php

namespace Codefog\NewsCategoriesBundle\EventListener\DataContainer;

use Contao\Backend;
use Contao\BackendUser;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use Contao\Image;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;

class NewsCategoryListener
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * NewsCategoryListener constructor.
     *
     * @param Connection               $connection
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(Connection $connection, ContaoFrameworkInterface $framework)
    {
        $this->connection = $connection;
        $this->framework = $framework;
    }

    /**
     * On data container load
     */
    public function onLoadCallback()
    {
        /** @var BackendUser $user */
        $user = $this->framework->createInstance(BackendUser::class);

        if (!$user->isAdmin && !$user->hasAccess('manage', 'newscategories')) {
            throw new AccessDeniedException(sprintf('User ID %s has no permissions to manage news categories', $user->id));
        }
    }

    /**
     * On paste button callback
     *
     * @param DataContainer $dc
     * @param array         $row
     * @param string        $table
     * @param boolean       $cr
     * @param array|null    $clipboard
     *
     * @return string
     */
    public function onPasteButtonCallback(DataContainer $dc, array $row, $table, $cr, array $clipboard = null)
    {
        $disablePA = false;
        $disablePI = false;

        // Disable all buttons if there is a circular reference
        if ($clipboard !== null && (
                ($clipboard['mode'] === 'cut' && ($cr || (int) $clipboard['id'] === (int) $row['id']))
                || ($clipboard['mode'] === 'cutAll' && ($cr || in_array($row['id'], $clipboard['id'])))
            )
        ) {
            $disablePA = true;
            $disablePI = true;
        }

        $return = '';

        if ($row['id'] > 0) {
            $return = $this->generatePasteImage('pasteafter', $disablePA, $table, $row, $clipboard);
        }

        return $return . $this->generatePasteImage('pasteinto', $disablePI, $table, $row, $clipboard);
    }

    /**
     * On label callback
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

        return $imageAdapter->getHtml('iconPLAIN.svg', '', $attributes) . ' ' . $label;
    }

    /**
     * On generate the category alias
     *
     * @param string        $value
     * @param DataContainer $dc
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
            $value = StringUtil::standardize(StringUtil::restoreBasicEntities($title));
        }

        $exists = $this->connection->fetchColumn('SELECT id FROM tl_news_category WHERE alias=? AND id!=?', [$value, $dc->id]);

        // Check whether the category alias exists
        if ($exists) {
            if ($autoAlias) {
                $value .= '-' . $dc->id;
            } else {
                throw new \RuntimeException(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $value));
            }
        }

        return $value;
    }

    /**
     * Generate the paste image
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
         * @var Backend $backendAdapter
         * @var Image   $imageAdapter
         */
        $backendAdapter = $this->framework->getAdapter(Backend::class);
        $imageAdapter = $this->framework->getAdapter(Image::class);

        if ($disabled) {
            return $imageAdapter->getHtml($type . '_.svg') . ' ';
        }

        $url = sprintf('act=%s&amp;mode=%s&amp;pid=%s', $clipboard['mode'], ($type === 'pasteafter' ? 1 : 0), $row['id']);

        // Add the ID to the URL if the clipboard does not contain any
        if (!is_array($clipboard['id'])) {
            $url .= '&amp;id=' . $clipboard['id'];
        }

        return sprintf(
            '<a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a> ',
            $backendAdapter->addToUrl($url),
            specialchars(sprintf($GLOBALS['TL_LANG'][$table][$type][1], $row['id'])),
            $imageAdapter->getHtml($type . '.svg', sprintf($GLOBALS['TL_LANG'][$table][$type][1], $row['id']))
        );
    }
}
