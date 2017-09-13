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
        $this->news_filterDefault = deserialize($this->news_filterDefault, true);

        return parent::generate();
    }

    /**
     * Generate the yearly menu
     */
    protected function compileYearlyMenu()
    {
        $time = time();
        $arrData = array();
        $arrNewsIds = $this->getFilteredNewsIds();
        
        // Configure template for yearly menu
        if (version_compare(VERSION, '4.0', '<'))
        {
            $this->Template = new \FrontendTemplate('mod_newsmenu_year');
        }
        else
        {
            $this->Template->yearly = true;
        }

        // Get the dates
        $objDates = $this->Database->query("SELECT FROM_UNIXTIME(date, '%Y') AS year, COUNT(*) AS count FROM tl_news WHERE pid IN(" . implode(',', array_map('intval', $this->news_archives)) . ")" . ((!BE_USER_LOGGED_IN || TL_MODE == 'BE') ? " AND (start='' OR start<$time) AND (stop='' OR stop>$time) AND published=1" : "") . (!empty($arrNewsIds) ? (" AND id IN (" . implode(',', $arrNewsIds) . ")") : "") . " GROUP BY year ORDER BY year DESC");

        while ($objDates->next())
        {
            $arrData[$objDates->year] = $objDates->count;
        }

        // Sort the data
        ($this->news_order == 'ascending') ? ksort($arrData) : krsort($arrData);

        $arrItems = array();
        $count = 0;
        $limit = count($arrData);
        $strUrl = $this->generateCategoryUrl();

        // Prepare the navigation
        foreach ($arrData as $intYear=>$intCount)
        {
            $intDate = $intYear;
            $quantity = sprintf((($intCount < 2) ? $GLOBALS['TL_LANG']['MSC']['entry'] : $GLOBALS['TL_LANG']['MSC']['entries']), $intCount);

            $arrItems[$intYear]['date'] = $intDate;
            $arrItems[$intYear]['link'] = $intYear;
            $arrItems[$intYear]['href'] = $strUrl . ($GLOBALS['TL_CONFIG']['disableAlias'] ? '&amp;' : '?') . 'year=' . $intDate;
            $arrItems[$intYear]['title'] = specialchars($intYear . ' (' . $quantity . ')');
            $arrItems[$intYear]['class'] = trim(((++$count == 1) ? 'first ' : '') . (($count == $limit) ? 'last' : ''));
            $arrItems[$intYear]['isActive'] = (\Input::get('year') == $intDate);
            $arrItems[$intYear]['quantity'] = $quantity;
        }

        $this->Template->items = $arrItems;
        $this->Template->showQuantity = ($this->news_showQuantity != '');
    }

    /**
     * Generate the monthly menu
     */
    protected function compileMonthlyMenu()
    {
        $time = time();
        $arrData = array();
        $arrNewsIds = $this->getFilteredNewsIds();

        // Get the dates
        $objDates = $this->Database->query("SELECT FROM_UNIXTIME(date, '%Y') AS year, FROM_UNIXTIME(date, '%m') AS month, COUNT(*) AS count FROM tl_news WHERE pid IN(" . implode(',', array_map('intval', $this->news_archives)) . ")" . ((!BE_USER_LOGGED_IN || TL_MODE == 'BE') ? " AND (start='' OR start<$time) AND (stop='' OR stop>$time) AND published=1" : "") . (!empty($arrNewsIds) ? (" AND id IN (" . implode(',', $arrNewsIds) . ")") : "") . " GROUP BY year, month ORDER BY year DESC, month DESC");

        while ($objDates->next())
        {
            $arrData[$objDates->year][$objDates->month] = $objDates->count;
        }

        // Sort the data
        foreach (array_keys($arrData) as $key)
        {
            ($this->news_order == 'ascending') ? ksort($arrData[$key]) : krsort($arrData[$key]);
        }

        ($this->news_order == 'ascending') ? ksort($arrData) : krsort($arrData);

        $strUrl = $this->generateCategoryUrl();
        $arrItems = array();

        // Prepare the navigation
        foreach ($arrData as $intYear=>$arrMonth)
        {
            $count = 0;
            $limit = count($arrMonth);

            foreach ($arrMonth as $intMonth=>$intCount)
            {
                $intDate = $intYear . $intMonth;
                $intMonth = (intval($intMonth) - 1);

                $quantity = sprintf((($intCount < 2) ? $GLOBALS['TL_LANG']['MSC']['entry'] : $GLOBALS['TL_LANG']['MSC']['entries']), $intCount);

                $arrItems[$intYear][$intMonth]['date'] = $intDate;
                $arrItems[$intYear][$intMonth]['link'] = $GLOBALS['TL_LANG']['MONTHS'][$intMonth] . ' ' . $intYear;
                $arrItems[$intYear][$intMonth]['href'] = $strUrl . ($GLOBALS['TL_CONFIG']['disableAlias'] ? '&amp;' : '?') . 'month=' . $intDate;
                $arrItems[$intYear][$intMonth]['title'] = specialchars($GLOBALS['TL_LANG']['MONTHS'][$intMonth].' '.$intYear . ' (' . $quantity . ')');
                $arrItems[$intYear][$intMonth]['class'] = trim(((++$count == 1) ? 'first ' : '') . (($count == $limit) ? 'last' : ''));
                $arrItems[$intYear][$intMonth]['isActive'] = (\Input::get('month') == $intDate);
                $arrItems[$intYear][$intMonth]['quantity'] = $quantity;
            }
        }

        $this->Template->items = $arrItems;
        $this->Template->showQuantity = ($this->news_showQuantity != '') ? true : false;
        $this->Template->url = $strUrl . ($GLOBALS['TL_CONFIG']['disableAlias'] ? '&amp;' : '?');
        $this->Template->activeYear = \Input::get('year');
    }

    /**
     * Generate the daily menu
     */
    protected function compileDailyMenu()
    {
        $time = time();
        $arrData = array();
        $arrNewsIds = $this->getFilteredNewsIds();
        
        // Configure template for daily menu
        if (version_compare(VERSION, '4.0', '<'))
        {
            $this->Template = new \FrontendTemplate('mod_newsmenu_day');
        }
        else
        {
            $this->Template->daily = true;
        }

        // Get the dates
        $objDates = $this->Database->query("SELECT FROM_UNIXTIME(date, '%Y%m%d') AS day, COUNT(*) AS count FROM tl_news WHERE pid IN(" . implode(',', array_map('intval', $this->news_archives)) . ")" . ((!BE_USER_LOGGED_IN || TL_MODE == 'BE') ? " AND (start='' OR start<$time) AND (stop='' OR stop>$time) AND published=1" : "") . (!empty($arrNewsIds) ? (" AND id IN (" . implode(',', $arrNewsIds) . ")") : "") . " GROUP BY day ORDER BY day DESC");

        while ($objDates->next())
        {
            $arrData[$objDates->day] = $objDates->count;
        }

        // Sort the data
        krsort($arrData);
        $strUrl = $this->generateCategoryUrl();

        $this->Date = \Input::get('day') ? new \Date(\Input::get('day'), 'Ymd') : new \Date();

        $intYear = date('Y', $this->Date->tstamp);
        $intMonth = date('m', $this->Date->tstamp);

        $this->Template->intYear = $intYear;
        $this->Template->intMonth = $intMonth;

        // Previous month
        $prevMonth = ($intMonth == 1) ? 12 : ($intMonth - 1);
        $prevYear = ($intMonth == 1) ? ($intYear - 1) : $intYear;
        $lblPrevious = $GLOBALS['TL_LANG']['MONTHS'][($prevMonth - 1)] . ' ' . $prevYear;

        $this->Template->prevHref = $strUrl . ($GLOBALS['TL_CONFIG']['disableAlias'] ? '?id=' . \Input::get('id') . '&amp;' : '?') . 'day=' . $prevYear . ((strlen($prevMonth) < 2) ? '0' : '') . $prevMonth . '01';
        $this->Template->prevTitle = specialchars($lblPrevious);
        $this->Template->prevLink = $GLOBALS['TL_LANG']['MSC']['news_previous'] . ' ' . $lblPrevious;
        $this->Template->prevLabel = $GLOBALS['TL_LANG']['MSC']['news_previous'];

        // Current month
        $this->Template->current = $GLOBALS['TL_LANG']['MONTHS'][(date('m', $this->Date->tstamp) - 1)] .  ' ' . date('Y', $this->Date->tstamp);

        // Next month
        $nextMonth = ($intMonth == 12) ? 1 : ($intMonth + 1);
        $nextYear = ($intMonth == 12) ? ($intYear + 1) : $intYear;
        $lblNext = $GLOBALS['TL_LANG']['MONTHS'][($nextMonth - 1)] . ' ' . $nextYear;

        $this->Template->nextHref = $strUrl . ($GLOBALS['TL_CONFIG']['disableAlias'] ? '?id=' . \Input::get('id') . '&amp;' : '?') . 'day=' . $nextYear . ((strlen($nextMonth) < 2) ? '0' : '') . $nextMonth . '01';
        $this->Template->nextTitle = specialchars($lblNext);
        $this->Template->nextLink = $lblNext . ' ' . $GLOBALS['TL_LANG']['MSC']['news_next'];
        $this->Template->nextLabel = $GLOBALS['TL_LANG']['MSC']['news_next'];

        // Set week start day
        if (!$this->news_startDay)
        {
            $this->news_startDay = 0;
        }

        $this->Template->days = $this->compileDays();
        $this->Template->weeks = $this->compileWeeks($arrData, $strUrl);

        $this->Template->showQuantity = ($this->news_showQuantity != '') ? true : false;
    }

    /**
     * Return all weeks of the current month as array
     * @param array
     * @return array
     */
    protected function compileWeeks($arrData)
    {
        $this->strUrl = $this->generateCategoryUrl();

        return parent::compileWeeks($arrData);
    }

    /**
     * Generate the menu URL with category
     * @return string
     */
    protected function generateCategoryUrl()
    {
        $strUrl = \Environment::get('script');

        // Get the current "jumpTo" page
        if (($objTarget = $this->objModel->getRelated('jumpTo')) !== null) {
            $varCategory = null;
            $strParam = NewsCategories::getParameterName();

            // Set the current category
            if (\Input::get($strParam)) {
                $varCategory = '/' . $strParam . '/' . \Input::get($strParam);
            }

            $strUrl = $this->generateFrontendUrl($objTarget->row(), $varCategory);
        }

        return $strUrl;
    }

    /**
     * Get the filtered news IDs
     *
     * @return array
     */
    protected function getFilteredNewsIds()
    {
        $arrCategories = \NewsModel::getCategoriesCache();

        if (empty($arrCategories)) {
            return array();
        }

        $arrIds = array();

        // Use the default filter
        if (is_array($this->news_filterDefault) && !empty($this->news_filterDefault)) {
            foreach ($this->news_filterDefault as $category) {
                if (isset($arrCategories[$category])) {
                    $arrIds = array_merge($arrCategories[$category], $arrIds);
                }
            }
        }

        $strParam = NewsCategories::getParameterName();

        // Current category
        if ($this->news_filterCategories && \Input::get($strParam)) {
            $strClass = \NewsCategories\NewsCategories::getModelClass();
            $objCategory = $strClass::findPublishedByIdOrAlias(\Input::get($strParam));

            if ($objCategory === null) {
                return array();
            }

            // Preserve the default filter
            if ($this->news_filterPreserve) {
                $arrIds = array_merge($arrCategories[$objCategory->id], $arrIds);
            } else {
                $arrIds = $arrCategories[$objCategory->id];
            }
        }

        return array_unique($arrIds);
    }
}
