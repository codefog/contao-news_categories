<?php

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

\Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('newsCategories_legend', 'global_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER, true)
    ->addField('newsCategories_param', 'newsCategories_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('root', 'tl_page');

/*
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_page']['fields']['newsCategories_param'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_page']['newsCategories_param'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['maxlength' => 64, 'rgxp' => 'alias', 'tl_class' => 'w50'],
    'sql' => ['type' => 'string', 'length' => 64],
];
