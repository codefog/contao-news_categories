<?php

/**
 * news_categories extension for Contao Open Source CMS
 *
 * Copyright (C) 2011-2014 Codefog
 *
 * @package news_categories
 * @author  Webcontext <http://webcontext.com>
 * @author  Codefog <info@codefog.pl>
 * @author  Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 * @license LGPL
 */

/**
 * Load tl_news_archive language file
 */
\System::loadLanguageFile('tl_news_archive');

/**
 * Table tl_news_category
 */
$GLOBALS['TL_DCA']['tl_news_category'] = [

    // Config
    'config'   => [
        'label'            => $GLOBALS['TL_LANG']['tl_news_archive']['categories'][0],
        'dataContainer'    => 'Table',
        'enableVersioning' => true,
        'onload_callback'  => [
            ['tl_news_category', 'checkPermission'],
        ],
        'sql'              => [
            'keys' => [
                'id'    => 'primary',
                'pid'   => 'index',
                'alias' => 'index',
            ],
        ],
        'backlink'         => 'do=news',
    ],

    // List
    'list'     => [
        'sorting'           => [
            'mode'                  => 5,
            'icon'                  => 'system/modules/news_categories/assets/icon.png',
            'paste_button_callback' => ['tl_news_category', 'pasteCategory'],
            'panelLayout'           => 'search',
        ],
        'label'             => [
            'fields'         => ['title', 'frontendTitle'],
            'format'         => '%s <span style="padding-left:3px;color:#b3b3b3;">[%s]</span>',
            'label_callback' => ['tl_news_category', 'generateLabel'],
        ],
        'global_operations' => [
            'toggleNodes' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['toggleAll'],
                'href'  => 'ptg=all',
                'class' => 'header_toggle',
            ],
            'all'         => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
        'operations'        => [
            'edit'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_news_category']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
            ],
            'copy'       => [
                'label'      => &$GLOBALS['TL_LANG']['tl_news_category']['copy'],
                'href'       => 'act=paste&amp;mode=copy',
                'icon'       => 'copy.gif',
                'attributes' => 'onclick="Backend.getScrollOffset()"',
            ],
            'copyChilds' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_news_category']['copyChilds'],
                'href'       => 'act=paste&amp;mode=copy&amp;childs=1',
                'icon'       => 'copychilds.gif',
                'attributes' => 'onclick="Backend.getScrollOffset()"',
            ],
            'cut'        => [
                'label'      => &$GLOBALS['TL_LANG']['tl_news_category']['cut'],
                'href'       => 'act=paste&amp;mode=cut',
                'icon'       => 'cut.gif',
                'attributes' => 'onclick="Backend.getScrollOffset()"',
            ],
            'delete'     => [
                'label'      => &$GLOBALS['TL_LANG']['tl_news_category']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
            ],
            'show'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_news_category']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
            'toggle'     => [
                'label'           => &$GLOBALS['TL_LANG']['tl_news_category']['toggle'],
                'icon'            => 'visible.gif',
                'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => ['tl_news_category', 'toggleIcon'],
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        'default' => '{title_legend},title,alias,frontendTitle,cssClass;{modules_legend:hide},hideInList,hideInReader,excludeInRelated;{redirect_legend:hide},jumpTo,jumpToNews;{publish_legend},published',
    ],

    // Fields
    'fields'   => [
        'id'               => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'pid'              => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'sorting'          => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'tstamp'           => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'title'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_news_category']['title'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'frontendTitle'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_news_category']['frontendTitle'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'alias'            => [
            'label'         => &$GLOBALS['TL_LANG']['tl_news_category']['alias'],
            'exclude'       => true,
            'search'        => true,
            'inputType'     => 'text',
            'eval'          => ['rgxp' => 'alias', 'unique' => true, 'spaceToUnderscore' => true, 'maxlength' => 128, 'tl_class' => 'w50'],
            'save_callback' => [
                ['tl_news_category', 'generateAlias'],
            ],
            'sql'           => "varbinary(128) NOT NULL default ''",
        ],
        'cssClass'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_news_category']['cssClass'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 128, 'tl_class' => 'w50'],
            'sql'       => "varchar(128) NOT NULL default ''",
        ],
        'hideInList'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_news_category']['hideInList'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'hideInReader'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_news_category']['hideInReader'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'excludeInRelated' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_news_category']['excludeInRelated'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'jumpTo'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_news_category']['jumpTo'],
            'exclude'   => true,
            'inputType' => 'pageTree',
            'eval'      => ['fieldType' => 'radio'],
            'sql'       => "int(10) unsigned NOT NULL default '0'",
            'relation'  => ['type' => 'hasOne', 'load' => 'eager', 'table' => 'tl_page'],
        ],
        'jumpToNews'           => [
            'label'        => &$GLOBALS['TL_LANG']['tl_news_category']['jumpToNews'],
            'exclude'      => true,
            'inputType'    => 'fieldpalette',
            'foreignKey'   => 'tl_fieldpalette.id',
            'relation'     => ['type' => 'hasMany', 'load' => 'eager'],
            'sql'          => "blob NULL",
            'fieldpalette' => [
                'config'   => [
                    'hidePublished' => false,
                ],
                'list'     => [
                    'label' => [
                        'fields'         => ['news_category_news_archive', 'news_category_jumpTo', 'news_category_news_jumpTo'],
                        'format'         => '%s -> %s [%s]',
                        'label_callback' => ['tl_news_category', 'generateJumpToDetailsLabel'],
                    ],
                ],
                'palettes' => [
                    'default' => 'news_category_news_archive,news_category_jumpTo,news_category_news_jumpTo',
                ],
                'fields'   => [
                    'news_category_news_archive' => [
                        'label'            => &$GLOBALS['TL_LANG']['tl_news_category']['news_category_news_archive'],
                        'inputType'        => 'select',
                        'options_callback' => ['tl_news_category', 'getNewsArchives'],
                        'eval'             => ['includeBlankOption' => true, 'mandatory' => true],
                        'sql'              => "int(10) unsigned NOT NULL default '0'",
                    ],
                    'news_category_jumpTo'       => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_news_category']['news_category_jumpTo'],
                        'exclude'   => true,
                        'inputType' => 'pageTree',
                        'eval'      => ['fieldType' => 'radio'],
                        'relation'  => ['type' => 'hasOne', 'load' => 'eager', 'table' => 'tl_page'],
                        'sql'       => "int(10) unsigned NOT NULL default '0'",
                    ],
                    'news_category_news_jumpTo'  => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_news_category']['news_category_news_jumpTo'],
                        'exclude'   => true,
                        'inputType' => 'pageTree',
                        'eval'      => ['fieldType' => 'radio'],
                        'relation'  => ['type' => 'hasOne', 'load' => 'eager', 'table' => 'tl_page'],
                        'sql'       => "int(10) unsigned NOT NULL default '0'",
                    ],
                ],
            ],
        ],
        'published'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_news_category']['published'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'sql'       => "char(1) NOT NULL default ''",
        ],
    ],
];

/**
 * Enable multilingual features
 */
if (\NewsCategories\NewsCategories::checkMultilingual())
{

    // Config
    $GLOBALS['TL_DCA']['tl_news_category']['config']['dataContainer']           = 'Multilingual';
    $GLOBALS['TL_DCA']['tl_news_category']['config']['language']                = \NewsCategories\NewsCategories::getAvailableLanguages();
    $GLOBALS['TL_DCA']['tl_news_category']['config']['langColumn']              = 'language';
    $GLOBALS['TL_DCA']['tl_news_category']['config']['langPid']                 = 'lid';
    $GLOBALS['TL_DCA']['tl_news_category']['config']['fallbackLang']            = \NewsCategories\NewsCategories::getFallbackLanguage();
    $GLOBALS['TL_DCA']['tl_news_category']['config']['sql']['keys']['language'] = 'index';
    $GLOBALS['TL_DCA']['tl_news_category']['config']['sql']['keys']['lid']      = 'index';

    // Fields
    $GLOBALS['TL_DCA']['tl_news_category']['fields']['language']['sql']                          = "varchar(2) NOT NULL default ''";
    $GLOBALS['TL_DCA']['tl_news_category']['fields']['lid']['sql']                               = "int(10) unsigned NOT NULL default '0'";
    $GLOBALS['TL_DCA']['tl_news_category']['fields']['title']['eval']['translatableFor']         = '*';
    $GLOBALS['TL_DCA']['tl_news_category']['fields']['frontendTitle']['eval']['translatableFor'] = '*';
}

class tl_news_category extends Backend
{

    /**
     * Check the permission
     */
    public function checkPermission()
    {
        $this->import('BackendUser', 'User');

        if (!$this->User->isAdmin && !$this->User->hasAccess('manage', 'newscategories'))
        {
            $this->redirect('contao/main.php?act=error');
        }
    }

    /**
     * Return the paste category button
     *
     * @param \DataContainer
     * @param array
     * @param string
     * @param boolean
     * @param array
     *
     * @return string
     */
    public function pasteCategory(DataContainer $dc, $row, $table, $cr, $arrClipboard = null)
    {
        $disablePA = false;
        $disablePI = false;

        // Disable all buttons if there is a circular reference
        if ($arrClipboard !== false
            && ($arrClipboard['mode'] == 'cut' && ($cr == 1 || $arrClipboard['id'] == $row['id'])
                || $arrClipboard['mode'] == 'cutAll'
                   && ($cr == 1
                       || in_array(
                           $row['id'],
                           $arrClipboard['id']
                       ))))
        {
            $disablePA = true;
            $disablePI = true;
        }

        $return = '';

        // Return the buttons
        $imagePasteAfter = Image::getHtml('pasteafter.gif', sprintf($GLOBALS['TL_LANG'][$table]['pasteafter'][1], $row['id']));
        $imagePasteInto  = Image::getHtml('pasteinto.gif', sprintf($GLOBALS['TL_LANG'][$table]['pasteinto'][1], $row['id']));

        if ($row['id'] > 0)
        {
            $return = $disablePA
                ? Image::getHtml('pasteafter_.gif') . ' '
                : '<a href="' . $this->addToUrl(
                    'act=' . $arrClipboard['mode'] . '&amp;mode=1&amp;pid=' . $row['id'] . (!is_array($arrClipboard['id']) ? '&amp;id=' . $arrClipboard['id'] : '')
                ) . '" title="' . specialchars(sprintf($GLOBALS['TL_LANG'][$table]['pasteafter'][1], $row['id'])) . '" onclick="Backend.getScrollOffset()">' . $imagePasteAfter
                  . '</a> ';
        }

        return $return . ($disablePI
                ? Image::getHtml('pasteinto_.gif') . ' '
                : '<a href="' . $this->addToUrl(
                    'act=' . $arrClipboard['mode'] . '&amp;mode=2&amp;pid=' . $row['id'] . (!is_array($arrClipboard['id']) ? '&amp;id=' . $arrClipboard['id'] : '')
                ) . '" title="' . specialchars(sprintf($GLOBALS['TL_LANG'][$table]['pasteinto'][1], $row['id'])) . '" onclick="Backend.getScrollOffset()">' . $imagePasteInto
                  . '</a> ');
    }

    /**
     * Add the correct indentation
     *
     * @param array
     * @param string
     * @param object
     * @param string
     *
     * @return string
     */
    public function generateLabel($arrRow, $strLabel, $objDca, $strAttributes)
    {
        return \Image::getHtml('iconPLAIN.gif', '', $strAttributes) . ' ' . $strLabel;
    }

    /**
     * Auto-generate the category alias if it has not been set yet
     *
     * @param mixed
     * @param \DataContainer
     *
     * @return string
     * @throws \Exception
     */
    public function generateAlias($varValue, DataContainer $dc)
    {
        $autoAlias = false;

        // Generate alias if there is none
        if (!strlen($varValue))
        {
            $autoAlias = true;
            $strTitle  = $dc->activeRecord->title;

            // Use the frontend title if available
            if (strlen($dc->activeRecord->frontendTitle))
            {
                $strTitle = $dc->activeRecord->frontendTitle;
            }

            $varValue = standardize($this->restoreBasicEntities($strTitle));
        }

        $objAlias = $this->Database->prepare("SELECT id FROM tl_news_category WHERE alias=?")->execute($varValue);

        // Check whether the category alias exists
        if ($objAlias->numRows > 1 && !$autoAlias)
        {
            throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
        }

        // Add ID to alias
        if ($objAlias->numRows && $autoAlias)
        {
            $varValue .= '-' . $dc->id;
        }

        return $varValue;
    }

    /**
     * Return the "toggle visibility" button
     *
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     *
     * @return string
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        if (strlen(Input::get('tid')))
        {
            $this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1));
            $this->redirect($this->getReferer());
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);

        if (!$row['published'])
        {
            $icon = 'invisible.gif';
        }

        return '<a href="' . $this->addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . '>' . $this->generateImage($icon, $label) . '</a> ';
    }

    /**
     * Publish/unpublish a category
     *
     * @param integer
     * @param boolean
     */
    public function toggleVisibility($intId, $blnVisible)
    {
        $objVersions = new Versions('tl_news_category', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_news_category']['fields']['published']['save_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_news_category']['fields']['published']['save_callback'] as $callback)
            {
                if (is_array($callback))
                {
                    $this->import($callback[0]);
                    $blnVisible = $this->$callback[0]->$callback[1]($blnVisible, $this);
                }
                elseif (is_callable($callback))
                {
                    $blnVisible = $callback($blnVisible, $this);
                }
            }
        }

        // Update the database
        $this->Database->prepare("UPDATE tl_news_category SET tstamp=" . time() . ", published='" . ($blnVisible ? 1 : '') . "' WHERE id=?")->execute($intId);

        $objVersions->create();
        $this->log('A new version of record "tl_news_category.id=' . $intId . '" has been created' . $this->getParentEntries('tl_news_category', $intId), __METHOD__, TL_GENERAL);
    }

    public function getNewsArchives(\DataContainer $dc)
    {
        $options = [];

        if (($archives = \NewsArchiveModel::findAll()) === null)
        {
            return $options;
        }

        $options = $archives->fetchEach('title');

        $existing = \HeimrichHannot\FieldPalette\FieldPaletteModel::findByPidAndTableAndField($dc->activeRecord->pid, 'tl_news_category', 'jumpTo');

        if ($existing !== null)
        {
            while ($existing->next())
            {
                if ($existing->id !== $dc->id)
                {
                    unset($options[$existing->news_category_news_archive]);
                }
            }
        }

        return $options;
    }

    /**
     * Generate the label for one news item
     *
     * @param array
     * @param string
     * @param object
     * @param string
     *
     * @return string
     */
    public function generateJumpToDetailsLabel($arrRow, $strLabel, $objDca, $strAttributes)
    {
        $archive  = \NewsArchiveModel::findByPk($arrRow['news_category_news_archive']);
        $page     = \PageModel::findByPk($arrRow['news_category_jumpTo']);
        $pageNews = \PageModel::findByPk($arrRow['news_category_news_jumpTo']);

        if ($archive === null || $page === null)
        {
            return $strLabel;
        }

        $strLabel = $archive->title . ' [' . $GLOBALS['TL_LANG']['tl_news_category']['news_category_jumpTo'][0] . '->' . $page->getFrontendUrl() . ']';

        if ($pageNews !== null)
        {
            $strLabel .= '[' . $GLOBALS['TL_LANG']['tl_news_category']['news_category_news_jumpTo'][0] . ' -> ' . $pageNews->getFrontendUrl() . ']';
        }

        return $strLabel;
    }
}
