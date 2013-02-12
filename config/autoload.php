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
 * Register the namespace
 */
ClassLoader::addNamespace('NewsCategories');


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Models
	'NewsCategories\NewsCategoryModel'    => 'system/modules/news_categories/models/NewsCategoryModel.php',
	'NewsCategories\NewsModel'            => 'system/modules/news_categories/models/NewsModel.php',

	// Modules
	'NewsCategories\ModuleNewsCategories' => 'system/modules/news_categories/modules/ModuleNewsCategories.php',
	'NewsCategories\ModuleNewsList'       => 'system/modules/news_categories/modules/ModuleNewsList.php'
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'mod_newscategories' => 'system/modules/news_categories/templates'
));
