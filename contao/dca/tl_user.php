<?php

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2024, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;

PaletteManipulator::create()
    ->addLegend('newsCategories_legend', 'news_legend', PaletteManipulator::POSITION_AFTER)
    ->addField('newscategories', 'newsCategories_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('newscategories_roots', 'newsCategories_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('newscategories_default', 'newsCategories_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('extend', 'tl_user')
    ->applyToPalette('custom', 'tl_user')
;

/*
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_user']['fields']['newscategories'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_user']['newscategories'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'options' => ['manage'],
    'reference' => &$GLOBALS['TL_LANG']['tl_user']['newscategoriesRef'],
    'eval' => ['multiple' => true, 'tl_class' => 'clr'],
    'sql' => ['type' => 'string', 'length' => 32, 'default' => ''],
];

$GLOBALS['TL_DCA']['tl_user']['fields']['newscategories_roots'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_user']['newscategories_roots'],
    'exclude' => true,
    'inputType' => 'picker',
    'foreignKey' => 'tl_news_category.title',
    'eval' => ['multiple' => true, 'fieldType' => 'checkbox'],
    'sql' => ['type' => 'blob', 'notnull' => false],
    'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_user']['fields']['newscategories_default'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_user']['newscategories_default'],
    'exclude' => true,
    'inputType' => 'picker',
    'foreignKey' => 'tl_news_category.title',
    'eval' => ['multiple' => true, 'fieldType' => 'checkbox'],
    'sql' => ['type' => 'blob', 'notnull' => false],
    'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
];
