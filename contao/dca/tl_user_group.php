<?php

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2023, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

use Contao\Controller;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\System;

Controller::loadDataContainer('tl_user');
System::loadLanguageFile('tl_user');

/*
 * Extend palettes
 */
PaletteManipulator::create()
    ->addLegend('newsCategories_legend', 'news_legend', PaletteManipulator::POSITION_AFTER)
    ->addField('newscategories', 'newsCategories_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('newscategories_roots', 'newsCategories_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('newscategories_default', 'newsCategories_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_user_group')
;

/*
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_user_group']['fields']['newscategories'] = &$GLOBALS['TL_DCA']['tl_user']['fields']['newscategories'];
$GLOBALS['TL_DCA']['tl_user_group']['fields']['newscategories_default'] = &$GLOBALS['TL_DCA']['tl_user']['fields']['newscategories_default'];
$GLOBALS['TL_DCA']['tl_user_group']['fields']['newscategories_roots'] = &$GLOBALS['TL_DCA']['tl_user']['fields']['newscategories_roots'];
