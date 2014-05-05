<?php

/**
 * news_categories extension for Contao Open Source CMS
 *
 * Copyright (C) 2013 Codefog
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
 * Override the default front end module "news menu".
 */
class ModuleNewsMenu extends \Contao\ModuleNewsMenu
{

    /**
     * Set the flag to filter news by categories
     * @return string
     */
    public function generate()
    {
        $GLOBALS['NEWS_FILTER_CATEGORIES'] = $this->news_filterCategories ? true : false;
        $GLOBALS['NEWS_FILTER_DEFAULT'] = deserialize($this->news_filterDefault, true);
        $GLOBALS['NEWS_FILTER_PRESERVE'] = $this->news_filterPreserve;

        return parent::generate();
    }


    /**
     * Generate the yearly menu
     */
    protected function compileYearlyMenu()
    {
        parent::compileYearlyMenu();

        if ($this->news_filterCategories)
        {
            $this->updateMenuLinks();
        }
    }


    /**
     * Generate the monthly menu
     */
    protected function compileMonthlyMenu()
    {
        parent::compileMonthlyMenu();

        if ($this->news_filterCategories)
        {
            $this->updateMenuLinks();
        }
    }


    /**
     * Update the menu links by adding a category
     */
    protected function updateMenuLinks()
    {
        $strUrl = $this->generateCategoryUrl();
        $arrItems = $this->Template->items;

        // Update the links
        foreach ($arrItems as $k => $v)
        {
            if (isset($v['href']))
            {
                $params = explode('?', $v['href']);
                $arrItems[$k]['href'] = $strUrl . '?' . $params[1];
            }
            else
            {
                foreach ($v as $kk => $vv)
                {
                    $params = explode('?', $vv['href']);
                    $arrItems[$k][$kk]['href'] = $strUrl . '?' . $params[1];
                }
            }
        }

        $this->Template->items = $arrItems;
    }


    /**
     * Generate the daily menu
     */
    protected function compileDailyMenu()
    {
        parent::compileDailyMenu();

        if ($this->news_filterCategories)
        {
            $prevParams = explode('?', $this->Template->prevHref);
            $this->Template->prevHref = $this->generateCategoryUrl() . '?' . $prevParams[1];

            $nextParams = explode('?', $this->Template->nextHref);
            $this->Template->nextHref = $this->generateCategoryUrl() . '?' . $nextParams[1];
        }
    }


    /**
     * Return all weeks of the current month as array
     * @param array
     * @param string
     * @return array
     */
    protected function compileWeeks($arrData, $strUrl)
    {
        return parent::compileWeeks($arrData, $this->generateCategoryUrl());
    }


    /**
     * Generate the menu URL with category
     * @return string
     */
    protected function generateCategoryUrl()
    {
        $strUrl = '';

        // Get the current "jumpTo" page
        if (($objTarget = $this->objModel->getRelated('jumpTo')) !== null)
        {
            $varCategory = null;

            // Set the current category
            if (\Input::get('category'))
            {
                $varCategory = '/category/' . \Input::get('category');
            }

            $strUrl = $this->generateFrontendUrl($objTarget->row(), $varCategory);
        }

        return $strUrl;
    }
}
