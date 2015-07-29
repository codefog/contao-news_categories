<?php

/**
 * news_categories extension for Contao Open Source CMS
 *
 * Copyright (C) 2011-2014 Codefog
 *
 * @package news_categories
 * @author  Webcontext <http://webcontext.com>
 * @author  Codefog <info@codefog.pl>
 * @author  Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 * @license LGPL
 */

/**
 * Load the tl_module data container
 */
\Controller::loadDataContainer('tl_module');
\System::loadLanguageFile('tl_module');

/**
 * Add palettes to tl_content
 */
$GLOBALS['TL_DCA']['tl_content']['palettes']['newsfilter'] = '{type_legend},type;{include_legend},news_module,news_filterCategories,news_filterDefault,news_filterPreserve;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';

/**
 * Add fields to tl_content
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['news_filterCategories'] = &$GLOBALS['TL_DCA']['tl_module']['fields']['news_filterCategories'];
$GLOBALS['TL_DCA']['tl_content']['fields']['news_filterDefault']    = &$GLOBALS['TL_DCA']['tl_module']['fields']['news_filterDefault'];
$GLOBALS['TL_DCA']['tl_content']['fields']['news_filterPreserve']   = &$GLOBALS['TL_DCA']['tl_module']['fields']['news_filterPreserve'];

$GLOBALS['TL_DCA']['tl_content']['fields']['news_module'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_content']['module'],
    'exclude'                 => true,
    'inputType'               => 'select',
    'options_callback'        => array('tl_content_newscategories', 'getNewsModules'),
    'eval'                    => array('mandatory'=>true, 'chosen'=>true, 'submitOnChange'=>true),
    'wizard' => array
    (
        array('tl_content', 'editModule')
    ),
    'sql'                     => "int(10) unsigned NOT NULL default '0'"
);

class tl_content_newscategories
{

    /**
     * Get news modules and return them as array
     *
     * @return array
     */
    public function getNewsModules()
    {
        $arrModules = array();
        $objModules = \Database::getInstance()->execute("SELECT m.id, m.name, t.name AS theme FROM tl_module m LEFT JOIN tl_theme t ON m.pid=t.id WHERE m.type IN ('newslist', 'newsarchive') ORDER BY t.name, m.name");

        while ($objModules->next()) {
            $arrModules[$objModules->theme][$objModules->id] = $objModules->name . ' (ID ' . $objModules->id . ')';
        }

        return $arrModules;
    }
}
