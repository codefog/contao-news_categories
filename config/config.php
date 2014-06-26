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
 * Extension version
 */
@define('NEWS_CATEGORIES_VERSION', '2.0');
@define('NEWS_CATEGORIES_BUILD', '0');

/**
 * Back end modules
 */
$GLOBALS['BE_MOD']['content']['news']['tables'][] = 'tl_news_category';
$GLOBALS['BE_MOD']['content']['news']['stylesheet'] = 'system/modules/news_categories/assets/backend.css';

/**
 * Front end modules
 */
$GLOBALS['FE_MOD']['news']['newscategories'] = 'ModuleNewsCategories';

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['parseArticles'][] = array('News', 'addCategoriesToTemplate');

/**
 * Add permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'newscategories';
$GLOBALS['TL_PERMISSIONS'][] = 'newscategories_default';
