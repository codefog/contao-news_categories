<?php

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2024, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

use Codefog\NewsCategoriesBundle\ContentElement\NewsFilterElement;
use Codefog\NewsCategoriesBundle\FrontendModule\CumulativeFilterModule;
use Codefog\NewsCategoriesBundle\FrontendModule\CumulativeHierarchicalFilterModule;
use Codefog\NewsCategoriesBundle\FrontendModule\NewsArchiveModule;
use Codefog\NewsCategoriesBundle\FrontendModule\NewsCategoriesModule;
use Codefog\NewsCategoriesBundle\FrontendModule\NewsListModule;
use Codefog\NewsCategoriesBundle\FrontendModule\NewsMenuModule;
use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;

$GLOBALS['BE_MOD']['content']['news']['tables'][] = 'tl_news_category';

/*
 * Front end modules
 */
$GLOBALS['FE_MOD']['news']['newsarchive'] = NewsArchiveModule::class;
$GLOBALS['FE_MOD']['news']['newscategories'] = NewsCategoriesModule::class;
$GLOBALS['FE_MOD']['news']['newscategories_cumulative'] = CumulativeFilterModule::class;
$GLOBALS['FE_MOD']['news']['newscategories_cumulativehierarchical'] = CumulativeHierarchicalFilterModule::class;
$GLOBALS['FE_MOD']['news']['newslist'] = NewsListModule::class;
$GLOBALS['FE_MOD']['news']['newsmenu'] = NewsMenuModule::class;

/*
 * Content elements
 */
$GLOBALS['TL_CTE']['includes']['newsfilter'] = NewsFilterElement::class;

/*
 * Models
 */
$GLOBALS['TL_MODELS']['tl_news_category'] = NewsCategoryModel::class;

/*
 * Add permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'newscategories';
$GLOBALS['TL_PERMISSIONS'][] = 'newscategories_default';
$GLOBALS['TL_PERMISSIONS'][] = 'newscategories_roots';
