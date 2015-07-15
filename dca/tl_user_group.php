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
 * Load tl_user language file
 */
\System::loadLanguageFile('tl_user');

/**
 * Add a palette to tl_user_group
 */
$GLOBALS['TL_DCA']['tl_user_group']['palettes']['default'] = str_replace('newsfeedp;', 'newsfeedp;{newscategories_legend},newscategories,newscategoriesp,newscategories_default;', $GLOBALS['TL_DCA']['tl_user_group']['palettes']['default']);

/**
 * Add a new field to tl_user_group
 */
$GLOBALS['TL_DCA']['tl_user_group']['fields']['newscategories'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_user']['newscategories'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'sql'                     => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['newscategories_default'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_user']['newscategories_default'],
    'exclude'                 => true,
    'inputType'               => 'treePicker',
    'foreignKey'              => 'tl_news_category.title',
    'eval'                    => array('multiple'=>true, 'fieldType'=>'checkbox', 'foreignTable'=>'tl_news_category', 'titleField'=>'title', 'searchField'=>'title', 'managerHref'=>'do=news&table=tl_news_category'),
    'sql'                     => "blob NULL"
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['newscategoriesp'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_user_group']['newscategoriesp'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'options'                 => array('create', 'delete'),
	'reference'               => &$GLOBALS['TL_LANG']['MSC'],
	'eval'                    => array('multiple'=>true),
	'sql'                     => "blob NULL"
);
