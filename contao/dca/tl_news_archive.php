<?php

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;

/*
 * Extend palettes
 */
$GLOBALS['TL_DCA']['tl_news_archive']['palettes']['__selector__'][] = 'limitCategories';
$GLOBALS['TL_DCA']['tl_news_archive']['subpalettes']['limitCategories'] = 'categories';

PaletteManipulator::create()
    ->addLegend('categories_legend', 'title_legend', PaletteManipulator::POSITION_AFTER)
    ->addField('limitCategories', 'categories_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_news_archive')
;

/*
 * Add fields to tl_news_archive
 */
$GLOBALS['TL_DCA']['tl_news_archive']['fields']['limitCategories'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_news_archive']['limitCategories'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true],
    'sql' => ['type' => 'boolean', 'default' => 0],
];

$GLOBALS['TL_DCA']['tl_news_archive']['fields']['categories'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_news_archive']['categories'],
    'exclude' => true,
    'filter' => true,
    'inputType' => 'picker',
    'foreignKey' => 'tl_news_category.title',
    // 'options_callback' => NewsCategoriesOptionsListener
    'eval' => ['mandatory' => true, 'multiple' => true, 'fieldType' => 'checkbox'],
    'sql' => ['type' => 'blob', 'notnull' => false],
    'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
];
