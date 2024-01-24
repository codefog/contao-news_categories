<?php

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2024, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

use Contao\System;

System::loadLanguageFile('tl_page');

$GLOBALS['TL_LANG']['tl_settings']['news_categories_legend'] = 'News categories';
$GLOBALS['TL_LANG']['tl_settings']['news_categorySlugSetting'] = &$GLOBALS['TL_LANG']['tl_page']['validAliasCharacters'];
