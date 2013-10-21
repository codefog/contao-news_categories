<?php

/**
 * news_categories extension for Contao Open Source CMS
 *
 * Copyright (C) 2013 Codefog Ltd
 *
 * @package news_categories
 * @author  Webcontext <http://webcontext.com>
 * @author  Codefog Ltd <info@codefog.pl>
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
$GLOBALS['TL_DCA']['tl_user_group']['palettes']['default'] = str_replace('newsfeedp;', 'newsfeedp,newscategories;', $GLOBALS['TL_DCA']['tl_user_group']['palettes']['default']);


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
