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

\Controller::loadDataContainer('tl_module');
\System::loadLanguageFile('tl_news');

$GLOBALS['TL_LANG']['tl_content']['news_categories_legend'] = $GLOBALS['TL_LANG']['tl_news']['category_legend'];

$GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][] = function(\DataContainer $dc) {
    
    // START CHECK if a compatible content element is in edit mode
    if (!$dc->id) {
        return;
    }
    $objContent = \ContentModel::findByPk($dc->id);

    if ($objContent === null || 'module' !== $objContent->type) {
        return;
    }
    
    $objModule   = \ModuleModel::findByPk($objContent->module);

    if ($objModule === null || 'newslist' !== $objModule->type || 'newsarchive' !== $objModule->type) {
        return;
    }
    // END CHECK END CHECK END CHECK END CHECK END CHECK

    // Add the palette for category filter
    $GLOBALS['TL_DCA']['tl_content']['palettes']['module'] = str_replace(
            'module;',
            'module;{news_categories_legend},news_filterCategories,news_filterDefault,news_filterPreserve;',
            $GLOBALS['TL_DCA']['tl_content']['palettes']['module']
    );

};

// Copy fields from tl_module-DCA to tl_content-DCA
$copyFields = array('news_filterCategories', 'news_filterDefault','news_filterPreserve');
foreach($copyFields as $fieldKey) {
   $GLOBALS['TL_DCA']['tl_content']['fields'][$fieldKey] = $GLOBALS['TL_DCA']['tl_module']['fields'][$fieldKey];
}


