<?php

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

$GLOBALS['BE_MOD']['content']['news']['tables'][] = 'tl_news_category';

/*
 * Front end modules
 */
$GLOBALS['FE_MOD']['news']['newsarchive'] = \Codefog\NewsCategoriesBundle\FrontendModule\NewsArchiveModule::class;
$GLOBALS['FE_MOD']['news']['newscategories'] = \Codefog\NewsCategoriesBundle\FrontendModule\NewsCategoriesModule::class;
$GLOBALS['FE_MOD']['news']['newscategories_cumulative'] = \Codefog\NewsCategoriesBundle\FrontendModule\CumulativeFilterModule::class;
$GLOBALS['FE_MOD']['news']['newscategories_cumulativehierarchical'] = \Codefog\NewsCategoriesBundle\FrontendModule\CumulativeHierarchicalFilterModule::class;
$GLOBALS['FE_MOD']['news']['newslist'] = \Codefog\NewsCategoriesBundle\FrontendModule\NewsListModule::class;
$GLOBALS['FE_MOD']['news']['newsmenu'] = \Codefog\NewsCategoriesBundle\FrontendModule\NewsMenuModule::class;

/*
 * Content elements
 */
$GLOBALS['TL_CTE']['includes']['newsfilter'] = \Codefog\NewsCategoriesBundle\ContentElement\NewsFilterElement::class;

/*
 * Models
 */
$GLOBALS['TL_MODELS']['tl_news_category'] = \Codefog\NewsCategoriesBundle\Model\NewsCategoryModel::class;

/*
 * Hooks
 */
$GLOBALS['TL_HOOKS']['changelanguageNavigation'][] = [
    'codefog_news_categories.listener.change_language',
    'onChangeLanguageNavigation',
];
$GLOBALS['TL_HOOKS']['newsListCountItems'][] = ['codefog_news_categories.listener.news', 'onNewsListCountItems'];
$GLOBALS['TL_HOOKS']['newsListFetchItems'][] = ['codefog_news_categories.listener.news', 'onNewsListFetchItems'];
$GLOBALS['TL_HOOKS']['parseArticles'][] = ['codefog_news_categories.listener.template', 'onParseArticles'];
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = ['codefog_news_categories.listener.insert_tags', 'onReplace'];

/*
 * Add permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'newscategories';
$GLOBALS['TL_PERMISSIONS'][] = 'newscategories_default';
$GLOBALS['TL_PERMISSIONS'][] = 'newscategories_roots';
