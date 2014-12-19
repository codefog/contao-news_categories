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

// Add the palette for content element newsfilter
$GLOBALS['TL_DCA']['tl_content']['palettes']['newsfilter'] = str_replace(
    'module;',
    'module;{news_categories_legend},news_filterCategories,news_filterDefault,news_filterPreserve;',
    $GLOBALS['TL_DCA']['tl_content']['palettes']['module']
);

// Copy fields from tl_module-DCA to tl_content-DCA
$copyFields = array('news_filterCategories', 'news_filterDefault','news_filterPreserve');
foreach($copyFields as $fieldKey) {
    $GLOBALS['TL_DCA']['tl_content']['fields'][$fieldKey] = $GLOBALS['TL_DCA']['tl_module']['fields'][$fieldKey];
}

$GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][] = function(\DataContainer $dc) {

    if (!$dc->id) {
        return;
    }
    $objContent = \ContentModel::findByPk($dc->id);

    if ($objContent === null || 'newsfilter' !== $objContent->type)
    {
        return;
    }

    // Set filter method for module field
    $GLOBALS['TL_DCA']['tl_content']['fields']['module']['options_callback'] = array('tl_content_newsfilter', 'getFilteredModules');

};


class tl_content_newsfilter extends tl_content
{

    public function getFilteredModules()
    {
        $arrModules = $this->getModules();

        // Filter modules
        foreach($arrModules as $theme => $arrThemeModules)
        {

            $arrModuleType = $this->getModuleTypesArray(array_keys($arrThemeModules));

            foreach($arrThemeModules as $moduleId => $strModuleLabel)
            {
                $strModuleType = $arrModuleType[$moduleId];
                // Show only newslist and newsarchive modules
                if ('newslist' !== $strModuleType && 'newsarchive' !== $strModuleType)
                {
                    unset($arrModules[$theme][$moduleId]);
                }
            }

            if (count($arrModules[$theme]) === 0)
            {
                // No newslist or newsarchive modules in the current theme array
                unset($arrModules[$theme]);
            }

        }
        return $arrModules;
    }

    private function getModuleTypesArray($arrIds)
    {
        $arrType          = array();
        $collectionModule = \ModuleModel::findMultipleByIds($arrIds);

        foreach($collectionModule as $objModule)
        {
            $arrType[$objModule->id] = $objModule->type;
        }
        return $arrType;
    }
}
