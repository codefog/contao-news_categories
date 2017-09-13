<?php

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
$GLOBALS['TL_HOOKS']['parseArticles'][] = ['News', 'addCategoriesToTemplate'];
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = ['codefog_news_categories.listener.insert_tags', 'onReplace'];

$GLOBALS['TL_HOOKS']['changelanguageNavigation'][] = [
    'codefog_news_categories.listener.change_language',
    'onChangeLanguageNavigation',
];

/**
 * Add permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'newscategories';
$GLOBALS['TL_PERMISSIONS'][] = 'newscategories_default';
