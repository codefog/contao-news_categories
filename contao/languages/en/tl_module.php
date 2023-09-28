<?php

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

/*
 * Fields.
 */
$GLOBALS['TL_LANG']['tl_module']['news_categories'] = ['News categories', 'Please choose the news categories.'];
$GLOBALS['TL_LANG']['tl_module']['news_customCategories'] = ['Limit categories display', 'Choose which categories should be displayed.'];
$GLOBALS['TL_LANG']['tl_module']['news_filterCategories'] = ['Filter by categories', 'Filter the news list by the categories list module.'];
$GLOBALS['TL_LANG']['tl_module']['news_filterCategoriesCumulative'] = ['Filter by categories (cumulative)', 'Filter the news list by the cumulative filter module. Takes priority over the previous checkbox for filter.'];
$GLOBALS['TL_LANG']['tl_module']['news_relatedCategories'] = ['Related categories mode', 'Use categories of the current news item to filter by. Note: the module must be on the same page as news reader module.'];
$GLOBALS['TL_LANG']['tl_module']['news_relatedCategoriesOrder'] = ['Related categories order', 'Here you can choose the related categories order.'];
$GLOBALS['TL_LANG']['tl_module']['news_includeSubcategories'] = ['Include the subcategories', 'Include the subcategories in the filtering if the parent category is active.'];
$GLOBALS['TL_LANG']['tl_module']['news_filterCategoriesUnion'] = ['Filter by categories using union (cumulative only)', 'Filter news and categories using union (x OR y) instead of intersection (x AND y).'];
$GLOBALS['TL_LANG']['tl_module']['news_enableCanonicalUrls'] = ['Enable canonical URLs', 'Adds the canonical URL tag when active category is present.'];
$GLOBALS['TL_LANG']['tl_module']['news_filterDefault'] = ['Default filter', 'Here you can choose the default filter that will be applied to the newslist.'];
$GLOBALS['TL_LANG']['tl_module']['news_filterPreserve'] = ['Preserve the default filter', 'Preserve the default filter settings even if there is an active category selected.'];
$GLOBALS['TL_LANG']['tl_module']['news_resetCategories'] = ['Reset categories link', 'Add a link to reset categories filter.'];
$GLOBALS['TL_LANG']['tl_module']['news_showEmptyCategories'] = ['Show empty categories', 'Show the categories even if they do not contain any news entries.'];
$GLOBALS['TL_LANG']['tl_module']['news_forceCategoryUrl'] = ['Force category URL', 'Use the category target page URL (if available) instead of the regular filter-link.'];
$GLOBALS['TL_LANG']['tl_module']['news_categoriesRoot'] = ['Reference category (root)', 'Here you can choose the reference category. It will be used as the starting point (similar to navigation module).'];
$GLOBALS['TL_LANG']['tl_module']['news_categoryFilterPage'] = ['Category target page', 'Here you can choose the news category target page that will override the category URLs with a filter-link to this page.'];
$GLOBALS['TL_LANG']['tl_module']['news_categoryImgSize'] = ['News category image size', &$GLOBALS['TL_LANG']['tl_module']['imgSize'][1]];

/*
 * Reference.
 */
$GLOBALS['TL_LANG']['tl_module']['news_relatedCategoriesOrderRef'] = [
    'default' => 'Default order',
    'best_match' => 'Best match',
];
