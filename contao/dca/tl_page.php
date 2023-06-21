<?php

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;

PaletteManipulator::create()
    ->addLegend('newsCategories_legend', 'global_legend', PaletteManipulator::POSITION_AFTER, true)
    ->addField('newsCategories_param', 'newsCategories_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('root', 'tl_page')
    ->applyToPalette('rootfallback', 'tl_page')
;

PaletteManipulator::create()
    ->addField(['newsCategories', 'newsCategories_show'], 'newsArchives')
    ->applyToPalette('news_feed', 'tl_page')
;

/*
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_page']['fields']['newsCategories_param'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_page']['newsCategories_param'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['maxlength' => 64, 'rgxp' => 'alias', 'tl_class' => 'w50'],
    'sql' => ['type' => 'string', 'length' => 64, 'default' => ''],
];

$GLOBALS['TL_DCA']['tl_page']['fields']['newsCategories'] = [
    'exclude' => true,
    'filter' => true,
    'inputType' => 'picker',
    'foreignKey' => 'tl_news_category.title',
    'eval' => ['multiple' => true, 'fieldType' => 'checkbox'],
    'sql' => ['type' => 'blob', 'notnull' => false],
    'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_page']['fields']['newsCategories_show'] = [
    'exclude' => true,
    'filter' => true,
    'inputType' => 'select',
    'options' => ['title', 'text_before', 'text_after'],
    'reference' => &$GLOBALS['TL_LANG']['tl_page']['newsCategories_show'],
    'eval' => [
        'includeBlankOption' => true,
        'blankOptionLabel' => $GLOBALS['TL_LANG']['tl_page']['newsCategories_show']['empty'] ?? null,
        'tl_class' => 'w50',
    ],
    'sql' => ['type' => 'string', 'length' => 16, 'default' => ''],
];
