<?php

/**
 * Load tl_user data container
 */
\Contao\Controller::loadDataContainer('tl_user');
\Contao\System::loadLanguageFile('tl_user');

/**
 * Extend palettes
 */
\Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('newsCategories_legend', 'news_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('newscategories', 'newsCategories_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->addField('newscategories_roots', 'newsCategories_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->addField('newscategories_default', 'newsCategories_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_user_group');

/**
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_user_group']['fields']['newscategories'] = &$GLOBALS['TL_DCA']['tl_user']['fields']['newscategories'];
$GLOBALS['TL_DCA']['tl_user_group']['fields']['newscategories_default'] = &$GLOBALS['TL_DCA']['tl_user']['fields']['newscategories_default'];
$GLOBALS['TL_DCA']['tl_user_group']['fields']['newscategories_roots'] = &$GLOBALS['TL_DCA']['tl_user']['fields']['newscategories_roots'];
