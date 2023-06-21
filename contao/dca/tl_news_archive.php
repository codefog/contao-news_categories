<?php

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

use Contao\ArrayUtil;

$GLOBALS['TL_DCA']['tl_news_archive']['config']['onload_callback'][] = [
    'codefog_news_categories.listener.data_container.news_archive',
    'onLoadCallback',
];

/*
 * Add global operations
 */
ArrayUtil::arrayInsert(
    $GLOBALS['TL_DCA']['tl_news_archive']['list']['global_operations'], 1, [
        'categories' => [
            'label' => &$GLOBALS['TL_LANG']['tl_news_archive']['categories'],
            'href' => 'table=tl_news_category',
            'icon' => 'bundles/codefognewscategories/icon.png',
            'attributes' => 'onclick="Backend.getScrollOffset()"',
        ],
    ]
);

/*
 * Extend palettes
 */
$GLOBALS['TL_DCA']['tl_news_archive']['palettes']['__selector__'][] = 'limitCategories';
$GLOBALS['TL_DCA']['tl_news_archive']['subpalettes']['limitCategories'] = 'categories';

\Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('categories_legend', 'title_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('limitCategories', 'categories_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_news_archive');

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
    'options_callback' => ['codefog_news_categories.listener.data_container.news', 'onCategoriesOptionsCallback'],
    'eval' => ['mandatory' => true, 'multiple' => true, 'fieldType' => 'checkbox'],
    'sql' => ['type' => 'blob', 'notnull' => false],
    'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
];
