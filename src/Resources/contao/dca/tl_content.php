<?php

/**
 * Load the tl_module data container
 */
\Contao\Controller::loadDataContainer('tl_module');
\Contao\System::loadLanguageFile('tl_module');

/**
 * Replace the feed generation callback
 */
if (\Contao\Input::get('do') === 'news'
    && ($index = array_search(['tl_content_news', 'generateFeed'] ,$GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'])) !== false
) {
    $GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][$index] = ['codefog_news_categories.listener.feed', 'onLoadCallback'];
}

/**
 * Add palettes
 */
$GLOBALS['TL_DCA']['tl_content']['palettes']['newsfilter'] = '{type_legend},type;{include_legend},news_module,news_filterCategories,news_filterDefault,news_filterPreserve;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';

/**
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['news_filterCategories'] = &$GLOBALS['TL_DCA']['tl_module']['fields']['news_filterCategories'];
$GLOBALS['TL_DCA']['tl_content']['fields']['news_filterDefault'] = &$GLOBALS['TL_DCA']['tl_module']['fields']['news_filterDefault'];
$GLOBALS['TL_DCA']['tl_content']['fields']['news_filterPreserve'] = &$GLOBALS['TL_DCA']['tl_module']['fields']['news_filterPreserve'];

$GLOBALS['TL_DCA']['tl_content']['fields']['news_module'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['module'],
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => ['codefog_news_categories.listener.data_container.content', 'onGetNewsModules'],
    'eval' => ['mandatory' => true, 'chosen' => true, 'submitOnChange' => true],
    'wizard' => [['tl_content', 'editModule']],
    'sql' => ['type' => 'integer', 'unsigned' => true],
];
