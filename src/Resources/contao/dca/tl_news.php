<?php

/*
 * News Categories Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

if (false !== ($index = array_search(['tl_news', 'generateFeed'], $GLOBALS['TL_DCA']['tl_news']['config']['onload_callback'], true))) {
    $GLOBALS['TL_DCA']['tl_news']['config']['onload_callback'][$index] = ['codefog_news_categories.listener.data_container.feed', 'onLoadCallback'];
}

/*
 * Add global callbacks
 */
$GLOBALS['TL_DCA']['tl_news']['config']['onload_callback'][] = ['codefog_news_categories.listener.data_container.news', 'onLoadCallback'];
$GLOBALS['TL_DCA']['tl_news']['config']['onsubmit_callback'][] = ['codefog_news_categories.listener.data_container.news', 'onSubmitCallback'];

/*
 * Extend palettes
 */
\Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('category_legend', 'title_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('categories', 'category_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_news');

/*
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_news']['fields']['categories'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_news']['categories'],
    'exclude' => true,
    'filter' => true,
    'inputType' => 'newsCategoriesPicker',
    'foreignKey' => 'tl_news_category.title',
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
