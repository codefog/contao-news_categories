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
 * Extension version
 */
@define('NEWS_CATEGORIES_VERSION', '1.0');
@define('NEWS_CATEGORIES_BUILD', '7');


/**
 * Back end modules
 */
$GLOBALS['BE_MOD']['content']['news']['tables'][] = 'tl_news_category';
$GLOBALS['BE_MOD']['content']['news']['stylesheet'] = 'system/modules/news_categories/assets/backend.css';


/**
 * Front end modules
 */
$GLOBALS['FE_MOD']['news']['newscategories'] = 'ModuleNewsCategories';
