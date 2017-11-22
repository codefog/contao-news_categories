<?php

/**
 * Extend palettes
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'news_customCategories';
$GLOBALS['TL_DCA']['tl_module']['palettes']['newscategories'] = '{title_legend},name,headline,type;{config_legend},news_archives,news_resetCategories,news_showEmptyCategories,news_showQuantity,news_categoriesRoot,news_customCategories;{redirect_legend:hide},jumpTo;{template_legend:hide},navigationTpl,customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['news_customCategories'] = 'news_categories';

\Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addField('news_relatedCategories', 'news_archives', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('news_filterPreserve', 'news_archives', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('news_filterDefault', 'news_archives', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('news_filterCategories', 'news_archives', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->applyToPalette('newslist', 'tl_module');

\Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addField('news_filterPreserve', 'news_archives', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('news_filterDefault', 'news_archives', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('news_filterCategories', 'news_archives', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->applyToPalette('newsarchive', 'tl_module')
    ->applyToPalette('newsmenu', 'tl_module');

/**
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['news_categories'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['news_categories'],
    'exclude' => true,
    'inputType' => 'newsCategoriesPicker',
    'foreignKey' => 'tl_news_category.title',
    'eval' => ['multiple' => true, 'fieldType' => 'checkbox'],
    'sql' => ['type' => 'blob'],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['news_customCategories'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['news_customCategories'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true, 'tl_class' => 'clr'],
    'sql' => ['type' => 'boolean'],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['news_filterCategories'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['news_filterCategories'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'clr'],
    'sql' => ['type' => 'boolean'],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['news_relatedCategories'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['news_relatedCategories'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'clr'],
    'sql' => ['type' => 'boolean'],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['news_filterDefault'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['news_filterDefault'],
    'exclude' => true,
    'inputType' => 'newsCategoriesPicker',
    'foreignKey' => 'tl_news_category.title',
    'eval' => ['multiple' => true, 'fieldType' => 'checkbox', 'tl_class' => 'clr'],
    'sql' => ['type' => 'blob'],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['news_filterPreserve'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['news_filterPreserve'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'clr'],
    'sql' => ['type' => 'boolean'],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['news_resetCategories'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['news_resetCategories'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
    'sql' => ['type' => 'boolean'],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['news_showEmptyCategories'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['news_showEmptyCategories'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
    'sql' => ['type' => 'boolean'],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['news_categoriesRoot'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['news_categoriesRoot'],
    'exclude' => true,
    'inputType' => 'newsCategoriesPicker',
    'foreignKey' => 'tl_news_category.title',
    'eval' => ['fieldType' => 'radio'],
    'sql' => ['type' => 'integer', 'unsigned' => true],
];
