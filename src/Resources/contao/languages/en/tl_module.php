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
 * Fields
 */
$GLOBALS['TL_LANG']['tl_module']['news_categories']        = array('News categories', 'Please choose the news categories.');
$GLOBALS['TL_LANG']['tl_module']['news_customCategories']  = array('Limit categories display', 'Choose which categories should be displayed.');
$GLOBALS['TL_LANG']['tl_module']['news_filterCategories']  = array('Filter by categories', 'Filter the news list by the categories list module.');
$GLOBALS['TL_LANG']['tl_module']['news_relatedCategories'] = array('Related categories mode', 'Use categories of the current news item to filter by. Note: the module must be on the same page as news reader module.');
$GLOBALS['TL_LANG']['tl_module']['news_includeSubcategories'] = array('Include the subcategories', 'Include the subcategories in the filtering if the parent category is active.');
$GLOBALS['TL_LANG']['tl_module']['news_filterDefault']     = array('Default filter', 'Here you can choose the default filter that will be applied to the newslist.');
$GLOBALS['TL_LANG']['tl_module']['news_filterPreserve']    = array('Preserve the default filter', 'Preserve the default filter settings even if there is an active category selected.');
$GLOBALS['TL_LANG']['tl_module']['news_resetCategories']   = array('Reset categories link', 'Add a link to reset categories filter.');
$GLOBALS['TL_LANG']['tl_module']['news_showEmptyCategories'] = array('Show empty categories', 'Show the categories even if they do not contain any news entries.');
$GLOBALS['TL_LANG']['tl_module']['news_forceCategoryUrl'] = array('Force category URL', 'Use the category target page URL (if available) instead of the regular filter-link.');
$GLOBALS['TL_LANG']['tl_module']['news_categoriesRoot']    = array('Reference category (root)', 'Here you can choose the reference category. It will be used as the starting point (similar to navigation module).');
$GLOBALS['TL_LANG']['tl_module']['news_categoryFilterPage'] = array('Category target page', 'Here you can choose the news category target page that will override the category URLs with a filter-link to this page.');
$GLOBALS['TL_LANG']['tl_module']['news_categoryImgSize']    = array('News category image size', &$GLOBALS['TL_LANG']['tl_module']['imgSize'][1]);
