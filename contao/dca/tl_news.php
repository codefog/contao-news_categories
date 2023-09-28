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
 * Add global callbacks
 */
$GLOBALS['TL_DCA']['tl_news']['config']['onsubmit_callback'][] = ['codefog_news_categories.listener.data_container.news', 'onSubmitCallback'];

/*
 * Extend palettes
 */
$paletteManipulator = PaletteManipulator::create()
    ->addLegend('category_legend', 'title_legend', PaletteManipulator::POSITION_AFTER)
    ->addField('categories', 'category_legend', PaletteManipulator::POSITION_APPEND)
;

foreach ($GLOBALS['TL_DCA']['tl_news']['palettes'] as $name => $palette) {
    if (is_string($palette)) {
        $paletteManipulator->applyToPalette($name, 'tl_news');
    }
}

/*
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_news']['fields']['categories'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_news']['categories'],
    'exclude' => true,
    'filter' => true,
    'inputType' => 'picker',
    'foreignKey' => 'tl_news_category.title',
    'options_callback' => ['codefog_news_categories.listener.data_container.news', 'onCategoriesOptionsCallback'],
    'eval' => ['multiple' => true, 'fieldType' => 'checkbox'],
    'relation' => [
        'type' => 'haste-ManyToMany',
        'load' => 'lazy',
        'table' => 'tl_news_category',
        'referenceColumn' => 'news_id',
        'fieldColumn' => 'category_id',
        'relationTable' => 'tl_news_categories',
    ],
];
