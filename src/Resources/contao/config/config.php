<?php

/**
 * Back end modules
 */
$GLOBALS['BE_MOD']['content']['news']['tables'][] = 'tl_news_category';

/**
 * Back end form fields
 */
$GLOBALS['BE_FFL']['newsCategoriesPicker'] = \Codefog\NewsCategoriesBundle\Widget\NewsCategoriesPickerWidget::class;

/**
 * Front end modules
 */
$GLOBALS['FE_MOD']['news']['newsarchive'] = \Codefog\NewsCategoriesBundle\FrontendModule\NewsArchiveModule::class;
$GLOBALS['FE_MOD']['news']['newscategories'] = \Codefog\NewsCategoriesBundle\FrontendModule\NewsCategoriesModule::class;
$GLOBALS['FE_MOD']['news']['newslist'] = \Codefog\NewsCategoriesBundle\FrontendModule\NewsListModule::class;
$GLOBALS['FE_MOD']['news']['newsmenu'] = \Codefog\NewsCategoriesBundle\FrontendModule\NewsMenuModule::class;

/**
 * Content elements
 */
$GLOBALS['TL_CTE']['includes']['newsfilter'] = \Codefog\NewsCategoriesBundle\ContentElement\NewsFilterElement::class;

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_news_category'] = \Codefog\NewsCategoriesBundle\Model\NewsCategoryModel::class;

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['changelanguageNavigation'][] = [
    'codefog_news_categories.listener.change_language',
    'onChangeLanguageNavigation',
];
$GLOBALS['TL_HOOKS']['executePostActions'][] = ['codefog_news_categories.listener.ajax', 'onExecutePostActions'];
$GLOBALS['TL_HOOKS']['newsListCountItems'][] = ['codefog_news_categories.listener.news', 'onNewsListCountItems'];
$GLOBALS['TL_HOOKS']['newsListFetchItems'][] = ['codefog_news_categories.listener.news', 'onNewsListFetchItems'];
$GLOBALS['TL_HOOKS']['parseArticles'][] = ['codefog_news_categories.listener.template', 'onParseArticles'];
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = ['codefog_news_categories.listener.insert_tags', 'onReplace'];

if (($index = array_search(['News', 'generateFeeds'], $GLOBALS['TL_HOOKS']['generateXmlFiles'])) !== false) {
    $GLOBALS['TL_HOOKS']['generateXmlFiles'][$index][0] = \Codefog\NewsCategoriesBundle\FeedGenerator::class;
}

/**
 * Cron jobs
 */
$GLOBALS['TL_CRON']['daily']['generateNewsFeeds'][0] = \Codefog\NewsCategoriesBundle\FeedGenerator::class;

/**
 * Add permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'newscategories';
$GLOBALS['TL_PERMISSIONS'][] = 'newscategories_default';
