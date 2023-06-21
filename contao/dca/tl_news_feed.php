<?php

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

if (false !== ($index = \array_search(['tl_news_feed', 'generateFeed'], $GLOBALS['TL_DCA']['tl_news_feed']['config']['onload_callback'], true))) {
    $GLOBALS['TL_DCA']['tl_news_feed']['config']['onload_callback'][$index] = ['codefog_news_categories.listener.data_container.feed', 'onLoadCallback'];
}

/*
 * Extend palettes
 */
\Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('newsCategories_legend', 'archives_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('categories', 'newsCategories_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->addField('categories_show', 'newsCategories_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_news_feed');

/*
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_news_feed']['fields']['categories'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_news_feed']['categories'],
    'exclude' => true,
    'filter' => true,
    'inputType' => 'picker',
    'foreignKey' => 'tl_news_category.title',
    'eval' => ['multiple' => true, 'fieldType' => 'checkbox'],
    'sql' => ['type' => 'blob', 'notnull' => false],
    'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_news_feed']['fields']['categories_show'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_news_feed']['categories_show'],
    'exclude' => true,
    'filter' => true,
    'inputType' => 'select',
    'options' => ['title', 'text_before', 'text_after'],
    'reference' => &$GLOBALS['TL_LANG']['tl_news_feed']['categories_show'],
    'eval' => [
        'includeBlankOption' => true,
        'blankOptionLabel' => isset($GLOBALS['TL_LANG']['tl_news_feed']['categories_show']['empty']) ? $GLOBALS['TL_LANG']['tl_news_feed']['categories_show']['empty'] : null,
        'tl_class' => 'w50',
    ],
    'sql' => ['type' => 'string', 'length' => 16, 'default' => ''],
];
