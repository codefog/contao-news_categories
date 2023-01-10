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
 * Add a palette to tl_user
 */
$GLOBALS['TL_DCA']['tl_user']['palettes']['extend'] = str_replace('newsfeedp;', 'newsfeedp;{newscategories_legend},newscategories,newscategories_default;', $GLOBALS['TL_DCA']['tl_user']['palettes']['extend']);
$GLOBALS['TL_DCA']['tl_user']['palettes']['custom'] = str_replace('newsfeedp;', 'newsfeedp;{newscategories_legend},newscategories,newscategories_default;', $GLOBALS['TL_DCA']['tl_user']['palettes']['custom']);

/**
 * Add a new field to tl_user
 */
$GLOBALS['TL_DCA']['tl_user']['fields']['newscategories'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_user']['newscategories'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'options'                 => ['manage'],
    'reference'               => &$GLOBALS['TL_LANG']['tl_user']['newscategoriesRef'],
    'eval'                    => ['multiple' => true, 'tl_class' => 'clr'],
    'sql'                     => "varchar(32) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_user']['fields']['newscategories_default'] = [
    'label'      => &$GLOBALS['TL_LANG']['tl_user']['newscategories_default'],
    'exclude'    => true,
    'inputType'  => 'picker',
    'foreignKey' => 'tl_news_category.title',
    'relation'   => ['tl_news_category'],
    'eval'       => ['multiple' => true, 'fieldType' => 'checkbox',],
    'sql'        => "blob NULL"
];
