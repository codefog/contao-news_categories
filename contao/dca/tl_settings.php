<?php

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2020, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\Slug\Slug;

if (\class_exists(Slug::class)) {
    $GLOBALS['TL_DCA']['tl_settings']['fields']['news_categorySlugSetting'] = [
        'inputType' => 'select',
        'options_callback' => ['codefog_news_categories.listener.data_container.settings', 'onSlugSettingOptionsCallback'],
        'eval' => ['tl_class' => 'w50', 'includeBlankOption' => true,  'decodeEntities' => true],
    ];

    PaletteManipulator::create()
        ->addLegend('news_categories_legend', null, PaletteManipulator::POSITION_AFTER, true)
        ->addField('news_categorySlugSetting', 'news_categories_legend', PaletteManipulator::POSITION_APPEND)
        ->applyToPalette('default', 'tl_settings')
    ;
}
