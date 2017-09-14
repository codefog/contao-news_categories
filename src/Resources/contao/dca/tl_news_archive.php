<?php

/**
 * Register the global callbacks
 */
$GLOBALS['TL_DCA']['tl_news_archive']['config']['onload_callback'][] = [
    'codefog_news_categories.listener.data_container.news_archive',
    'onLoadCallback',
];

/**
 * Add global operations
 */
array_insert(
    $GLOBALS['TL_DCA']['tl_news_archive']['list']['global_operations'], 1, [
        'categories' => [
            'label' => &$GLOBALS['TL_LANG']['tl_news_archive']['categories'],
            'href' => 'table=tl_news_category',
            'icon' => 'bundles/codefognewscategories/icon.png',
            'attributes' => 'onclick="Backend.getScrollOffset()"',
        ],
    ]
);

/**
 * Extend palettes
 */
$GLOBALS['TL_DCA']['tl_news_archive']['palettes']['__selector__'][] = 'limitCategories';
$GLOBALS['TL_DCA']['tl_news_archive']['subpalettes']['limitCategories'] = 'categories';

\Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('categories_legend', 'title_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('limitCategories', 'categories_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_news_archive');

/**
 * Add fields to tl_news_archive
 */
$GLOBALS['TL_DCA']['tl_news_archive']['fields']['limitCategories'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_news_archive']['limitCategories'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true],
    'sql' => ['type' => 'boolean'],
];

$GLOBALS['TL_DCA']['tl_news_archive']['fields']['categories'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_news_archive']['categories'],
    'exclude' => true,
    'inputType' => 'treePicker',
    'foreignKey' => 'tl_news_category.title',
    'eval' => [
        'mandatory' => true,
        'multiple' => true,
        'fieldType' => 'checkbox',
        'foreignTable' => 'tl_news_category',
        'titleField' => 'title',
        'searchField' => 'title',
        'managerHref' => 'do=news&table=tl_news_category',
    ],
    'sql' => ['type' => 'blob'],
];
