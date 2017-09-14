<?php

/**
 * Add global callbacks
 */
$GLOBALS['TL_DCA']['tl_news']['config']['onload_callback'][] = ['codefog_news_categories.listener.data_container.news', 'onLoadCallback'];
$GLOBALS['TL_DCA']['tl_news']['config']['oncopy_callback'][] = ['codefog_news_categories.listener.data_container.news', 'onCopyCallback'];
$GLOBALS['TL_DCA']['tl_news']['config']['onsubmit_callback'][] = ['codefog_news_categories.listener.data_container.news', 'onSubmitCallback'];
$GLOBALS['TL_DCA']['tl_news']['config']['ondelete_callback'][] = ['codefog_news_categories.listener.data_container.news', 'onDeleteCallback'];

/**
 * Extend palettes
 */
\Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('category_legend', 'title_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('categories', 'category_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_news');

/**
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_news']['fields']['categories'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_news']['categories'],
    'exclude' => true,
    'filter' => true,
    'inputType' => 'treePicker',
    'foreignKey' => 'tl_news_category.title',
    'eval' => [
        'multiple' => true,
        'fieldType' => 'checkbox',
        'foreignTable' => 'tl_news_category',
        'titleField' => 'title',
        'searchField' => 'title',
        'managerHref' => 'do=news&table=tl_news_category',
    ],
    'sql' => ['type' => 'blob'],
];
