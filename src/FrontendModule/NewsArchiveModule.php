<?php

namespace Codefog\NewsCategoriesBundle\FrontendModule;

use Contao\ModuleNewsArchive;

class NewsArchiveModule extends ModuleNewsArchive
{
    /**
     * Set the flag to filter news by categories
     *
     * @return string
     */
    public function generate()
    {
        $GLOBALS['NEWS_FILTER_CATEGORIES'] = $this->news_filterCategories ? true : false;
        $GLOBALS['NEWS_FILTER_DEFAULT'] = deserialize($this->news_filterDefault, true);
        $GLOBALS['NEWS_FILTER_PRESERVE'] = $this->news_filterPreserve;

        return parent::generate();
    }
}
