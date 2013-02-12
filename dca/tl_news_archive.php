<?php

/**
 * news_categories extension for Contao Open Source CMS
 * 
 * Copyright (C) 2013 Codefog
 * 
 * @package news_categories
 * @link    http://codefog.pl
 * @author  Webcontext <http://webcontext.com>
 * @author  Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 * @license LGPL
 */


/**
 * Add a global operation to tl_news_archive
 */
array_insert($GLOBALS['TL_DCA']['tl_news_archive']['list']['global_operations'], 1, array
(
	'categories' => array
	(
		'label'               => &$GLOBALS['TL_LANG']['tl_news_archive']['categories'],
		'href'                => 'table=tl_news_category',
		'class'               => 'header_categories',
		'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="c"'
	)
));
