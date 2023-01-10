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
 * Add palettes to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][]           = 'news_customCategories';
$GLOBALS['TL_DCA']['tl_module']['palettes']['newscategories']           = '{title_legend},name,headline,type;{config_legend},news_archives,news_resetCategories,news_showQuantity,news_categoriesRoot,news_customCategories;{redirect_legend:hide},jumpTo;{template_legend:hide},navigationTpl,customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['news_customCategories'] = 'news_categories';

/**
 * Extend tl_module palettes
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['newslist']    = str_replace('news_archives,', 'news_archives,news_filterCategories,news_relatedCategories,news_filterDefault,news_filterStopLevel,news_filterPreserve,news_filterPrimaryCategory,', $GLOBALS['TL_DCA']['tl_module']['palettes']['newslist']);
$GLOBALS['TL_DCA']['tl_module']['palettes']['newsarchive'] = str_replace('news_archives,', 'news_archives,news_filterCategories,news_filterDefault,news_filterPreserve,news_filterStopLevel,news_filterPrimaryCategory,', $GLOBALS['TL_DCA']['tl_module']['palettes']['newsarchive']);
$GLOBALS['TL_DCA']['tl_module']['palettes']['newsmenu']    = str_replace('news_archives,', 'news_archives,news_filterCategories,news_filterDefault,news_filterPreserve,news_filterStopLevel,news_filterPrimaryCategory,', $GLOBALS['TL_DCA']['tl_module']['palettes']['newsmenu']);

/**
 * Add new fields to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['news_categories'] = [
    'label'      => &$GLOBALS['TL_LANG']['tl_module']['news_categories'],
    'exclude'    => true,
    'inputType'  => 'picker',
    'foreignKey' => 'tl_news_category.title',
    'relation' => ['tl_news_category'],
    'eval'       => ['mandatory' => true, 'multiple' => true, 'fieldType' => 'checkbox',],
    'sql'        => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['news_customCategories'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['news_customCategories'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['submitOnChange' => true, 'tl_class' => 'clr'],
    'sql'       => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['news_filterCategories'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['news_filterCategories'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'w50'],
    'sql'       => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['news_relatedCategories'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['news_relatedCategories'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'w50'],
    'sql'       => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['news_filterDefault'] = [
    'label'      => &$GLOBALS['TL_LANG']['tl_module']['news_filterDefault'],
    'exclude'    => true,
    'inputType'  => 'picker',
    'foreignKey' => 'tl_news_category.title',
    'relation' => ['tl_news_category'],
    'eval'       => ['multiple' => true, 'fieldType' => 'checkbox', 'tl_class' => 'clr'],
    'sql'        => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['news_filterStopLevel'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['news_filterStopLevel'],
    'exclude'   => true,
    'inputType' => 'text',
    'default'   => 0,
    'sql'       => "tinyint NOT NULL default '0'",
    'eval'      => ['rgxp' => 'natural', 'tl_class' => 'clr w50']
];

$GLOBALS['TL_DCA']['tl_module']['fields']['news_filterPreserve'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['news_filterPreserve'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'sql'       => "char(1) NOT NULL default ''",
    'eval'      => ['tl_class' => 'clr w50']
];


$GLOBALS['TL_DCA']['tl_module']['fields']['news_resetCategories'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['news_resetCategories'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'w50'],
    'sql'       => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['news_categoriesRoot'] = [
    'label'      => &$GLOBALS['TL_LANG']['tl_module']['news_categoriesRoot'],
    'exclude'    => true,
    'inputType'  => 'picker',
    'foreignKey' => 'tl_news_category.title',
    'relation'   => ['tl_news_category'],
    'eval'       => ['fieldType' => 'radio',],
    'sql'        => "int(10) unsigned NOT NULL default '0'"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['news_filterPrimaryCategory'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['news_filterPrimaryCategory'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'w50'],
    'sql'       => "char(1) NOT NULL default ''"
];