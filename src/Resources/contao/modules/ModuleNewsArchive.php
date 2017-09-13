<?php

/**
 * news_categories extension for Contao Open Source CMS
 *
 * Copyright (C) 2011-2014 Codefog
 *
 * @package news_categories
 * @link    http://codefog.pl
 * @author  Webcontext <http://webcontext.com>
 * @author  Codefog <info@codefog.pl>
 * @author  Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 * @license LGPL
 */

namespace NewsCategories;

/**
 * Override the default front end module "news archive".
 */
class ModuleNewsArchive extends \Contao\ModuleNewsArchive
{

    /**
     * Set the flag to filter news by categories
     * @return string
     */
    public function generate()
    {
        $GLOBALS['NEWS_FILTER_CATEGORIES'] = $this->news_filterCategories ? true : false;
        $GLOBALS['NEWS_FILTER_DEFAULT']    = deserialize($this->news_filterDefault, true);
        $GLOBALS['NEWS_FILTER_PRESERVE']   = $this->news_filterPreserve;

        return parent::generate();
    }
}
