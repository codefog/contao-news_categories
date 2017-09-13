<?php

/**
 * Extend palettes
 */
\Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('newsCategories_legend', 'news_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('newscategories', 'newsCategories_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->addField('newscategories_default', 'newsCategories_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('extend', 'tl_user')
    ->applyToPalette('custom', 'tl_user');

/**
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_user']['fields']['newscategories'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_user']['newscategories'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'options' => ['manage'],
    'reference' => &$GLOBALS['TL_LANG']['tl_user']['newscategoriesRef'],
    'eval' => ['multiple' => true, 'tl_class' => 'clr'],
    'sql' => ['type' => 'string', 'length' => 32],
];

$GLOBALS['TL_DCA']['tl_user']['fields']['newscategories_default'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_user']['newscategories_default'],
    'exclude' => true,
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
