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
 * Register the global callbacks
 */
$GLOBALS['TL_DCA']['tl_news_archive']['config']['onload_callback'][] = array('tl_news_archive_categories', 'checkPermission');
$GLOBALS['TL_DCA']['tl_news_archive']['config']['onload_callback'][] = array('tl_news_archive_categories', 'adjustPalette');

/**
 * Add a global operation to tl_news_archive
 */
array_insert($GLOBALS['TL_DCA']['tl_news_archive']['list']['global_operations'], 1, array
(
    'categories' => array
    (
        'label'               => &$GLOBALS['TL_LANG']['tl_news_archive']['categories'],
        'href'                => 'table=tl_news_category',
        'icon'                => 'system/modules/news_categories/assets/icon.png',
        'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="c"'
    )
));

/**
 * Add palettes to tl_news_archive
 */
$GLOBALS['TL_DCA']['tl_news_archive']['palettes']['default'] = str_replace('jumpTo;', 'jumpTo;{categories_legend},limitCategories;', $GLOBALS['TL_DCA']['tl_news_archive']['palettes']['default']);

/**
 * Add fields to tl_news_archive
 */
$GLOBALS['TL_DCA']['tl_news_archive']['fields']['limitCategories'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_news_archive']['limitCategories'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array('submitOnChange'=>true),
    'sql'                     => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_news_archive']['fields']['categories'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_news_archive']['categories'],
    'exclude'                 => true,
    'inputType'               => 'treePicker',
    'foreignKey'              => 'tl_news_category.title',
    'eval'                    => array('mandatory'=>true, 'multiple'=>true, 'fieldType'=>'checkbox', 'foreignTable'=>'tl_news_category', 'titleField'=>'title', 'searchField'=>'title', 'managerHref'=>'do=news&table=tl_news_category'),
    'sql'                     => "blob NULL"
);

class tl_news_archive_categories extends Backend
{

    /**
     * Check the permission
     */
    public function checkPermission()
    {
        $this->import('BackendUser', 'User');

        if (!$this->User->isAdmin && !$this->User->hasAccess('manage', 'newscategories')) {
            unset($GLOBALS['TL_DCA']['tl_news_archive']['list']['global_operations']['categories']);
        }
    }

    /**
     * Adjust the palette
     */
    public function adjustPalette($dc=null)
    {
        if (!$dc->id) {
            return;
        }

        $objArchive = $this->Database->prepare("SELECT limitCategories FROM tl_news_archive WHERE id=?")
                                     ->limit(1)
                                     ->execute($dc->id);

        if (!$objArchive->numRows || !$objArchive->limitCategories) {
            return;
        }

        $GLOBALS['TL_DCA']['tl_news_archive']['palettes']['default'] = str_replace('limitCategories;', 'limitCategories,categories;', $GLOBALS['TL_DCA']['tl_news_archive']['palettes']['default']);
    }
}
