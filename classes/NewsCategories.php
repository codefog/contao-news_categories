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

namespace NewsCategories;


/**
 * Provide methods regarding news categories
 */
class NewsCategories
{

    /**
     * Check if the system is multilingual installed
     * @return boolean
     */
    public static function checkMultilingual()
    {
        return (file_exists(TL_ROOT . '/system/modules/dc_multilingual/drivers/DC_Multilingual.php') && count(static::getAvailableLanguages()) > 1) ? true : false;
    }


    /**
     * Return a list of available languages
     * @return array
     */
    public static function getAvailableLanguages()
    {
        return \Database::getInstance()->execute("SELECT DISTINCT(language) FROM tl_page WHERE type='root'")->fetchEach('language');
    }


    /**
     * Get a fallback language
     * @return string
     */
    public static function getFallbackLanguage()
    {
        return \Database::getInstance()->execute("SELECT language FROM tl_page WHERE type='root' AND fallback=1")->language;
    }


    /**
     * Get the model class name
     * @return string
     */
    public static function getModelClass()
    {
        return static::checkMultilingual() ? '\NewsCategoryMultilingualModel' : '\NewsCategoryModel';
    }
}
