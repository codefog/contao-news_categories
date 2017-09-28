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
@define('NEWS_CATEGORIES_VERSION', '2.8');
@define('NEWS_CATEGORIES_BUILD', '1');

/**
 * Back end modules
 */
$GLOBALS['BE_MOD']['content']['news']['tables'][] = 'tl_news_category';

/**
 * Front end modules
 */
$GLOBALS['FE_MOD']['news']['newscategories'] = 'ModuleNewsCategories';

/**
 * Content elements
 */
$GLOBALS['TL_CTE']['includes']['newsfilter'] = 'ContentNewsFilter';

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['parseArticles']['news_categories']     = ['News', 'addCategoriesToTemplate'];
$GLOBALS['TL_HOOKS']['replaceInsertTags']['news_categories'] = ['News', 'parseCategoriesTags'];
$GLOBALS['TL_HOOKS']['replaceInsertTags']['news_categories'] = ['NewsCategories\InsertTags', 'replace'];


if (in_array('changelanguage', \ModuleLoader::getActive()))
{
    $GLOBALS['TL_HOOKS']['translateUrlParameters'][] = ['NewsCategories', 'translateUrlParameters'];
}

foreach ($GLOBALS['TL_HOOKS']['getSearchablePages'] as &$arrCallback)
{
    if ($arrCallback[0] == 'News' && $arrCallback[1] == 'getSearchablePages')
    {
        $arrCallback = ['NewsCategories\News', 'getSearchablePages'];
    }
}

/**
 * Add permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'newscategories';
$GLOBALS['TL_PERMISSIONS'][] = 'newscategories_default';
