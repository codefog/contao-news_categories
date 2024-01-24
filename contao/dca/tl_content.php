<?php

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2024, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

use Contao\Controller;
use Contao\System;

Controller::loadDataContainer('tl_module');
System::loadLanguageFile('tl_module');

/*
 * Add palettes
 */
$GLOBALS['TL_DCA']['tl_content']['palettes']['newsfilter'] = '{type_legend},type,headline;{include_legend},news_module,news_filterCategories,news_relatedCategories,news_filterDefault,news_filterPreserve;{link_legend:hide},news_categoryFilterPage;{image_legend:hide},news_categoryImgSize;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';

/*
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['news_filterCategories'] = &$GLOBALS['TL_DCA']['tl_module']['fields']['news_filterCategories'];
$GLOBALS['TL_DCA']['tl_content']['fields']['news_relatedCategories'] = &$GLOBALS['TL_DCA']['tl_module']['fields']['news_relatedCategories'];
$GLOBALS['TL_DCA']['tl_content']['fields']['news_includeSubcategories'] = &$GLOBALS['TL_DCA']['tl_module']['fields']['news_includeSubcategories'];
$GLOBALS['TL_DCA']['tl_content']['fields']['news_filterDefault'] = &$GLOBALS['TL_DCA']['tl_module']['fields']['news_filterDefault'];
$GLOBALS['TL_DCA']['tl_content']['fields']['news_filterPreserve'] = &$GLOBALS['TL_DCA']['tl_module']['fields']['news_filterPreserve'];
$GLOBALS['TL_DCA']['tl_content']['fields']['news_categoryFilterPage'] = &$GLOBALS['TL_DCA']['tl_module']['fields']['news_categoryFilterPage'];
$GLOBALS['TL_DCA']['tl_content']['fields']['news_categoryImgSize'] = &$GLOBALS['TL_DCA']['tl_module']['fields']['news_categoryImgSize'];

$GLOBALS['TL_DCA']['tl_content']['fields']['news_module'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['module'],
    'exclude' => true,
    'inputType' => 'select',
    // 'options_callback' => ContentNewsModuleOptionsListener
    'eval' => ['mandatory' => true, 'chosen' => true, 'submitOnChange' => true],
    'wizard' => [['tl_content', 'editModule']],
    'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
];
