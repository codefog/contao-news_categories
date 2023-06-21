<?php

declare(strict_types=1);

/*
 * News Categories Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\FrontendModule;

use Codefog\NewsCategoriesBundle\Exception\CategoryNotFoundException;
use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\Database;
use Contao\Input;
use Contao\ModuleNewsMenu;
use Contao\PageModel;
use Contao\System;

class NewsMenuModule extends ModuleNewsMenu
{
    /**
     * {@inheritDoc}
     */
    protected function compile(): void
    {
        $this->strUrl = $this->generateCategoryUrl();

        // Support Contao 4.5 news module
        $this->news_order = 'order_date_asc' === $this->news_order ? 'ascending' : $this->news_order;

        parent::compile();
    }

    /**
     * Generate the yearly menu.
     */
    protected function compileYearlyMenu(): void
    {
        $arrData = [];
        $time = \Date::floorToMinute();
        $newsIds = $this->getFilteredNewsIds();

        // Get the dates
        $objDates = $this->Database->query("SELECT FROM_UNIXTIME(date, '%Y') AS year, COUNT(*) AS count FROM tl_news WHERE pid IN(".implode(',', array_map('intval', $this->news_archives)).')'.(!System::getContainer()->get('contao.security.token_checker')->isPreviewMode() ? " AND (start='' OR start<='$time') AND (stop='' OR stop>'".($time + 60)."') AND published='1'" : '').(\count($newsIds) > 0 ? (' AND id IN ('.implode(',', $newsIds).')') : '').' GROUP BY year ORDER BY year DESC');

        while ($objDates->next()) {
            $arrData[$objDates->year] = $objDates->count;
        }

        // Sort the data
        ('ascending' === $this->news_order) ? ksort($arrData) : krsort($arrData);

        $arrItems = [];
        $count = 0;
        $limit = \count($arrData);

        // Prepare the navigation
        foreach ($arrData as $intYear => $intCount) {
            $intDate = $intYear;
            $quantity = sprintf($intCount < 2 ? $GLOBALS['TL_LANG']['MSC']['entry'] : $GLOBALS['TL_LANG']['MSC']['entries'], $intCount);

            $arrItems[$intYear]['date'] = $intDate;
            $arrItems[$intYear]['link'] = $intYear;
            $arrItems[$intYear]['href'] = $this->strUrl.'?year='.$intDate;
            $arrItems[$intYear]['title'] = \StringUtil::specialchars($intYear.' ('.$quantity.')');
            $arrItems[$intYear]['class'] = trim((1 === ++$count ? 'first ' : '').($count === $limit ? 'last' : ''));
            $arrItems[$intYear]['isActive'] = \Input::get('year') === $intDate;
            $arrItems[$intYear]['quantity'] = $quantity;
        }

        $this->Template->yearly = true;
        $this->Template->items = $arrItems;
        $this->Template->showQuantity = '' !== $this->news_showQuantity;
    }

    /**
     * Generate the monthly menu.
     */
    protected function compileMonthlyMenu(): void
    {
        $arrData = [];
        $time = \Date::floorToMinute();
        $newsIds = $this->getFilteredNewsIds();

        // Get the dates
        $objDates = $this->Database->query("SELECT FROM_UNIXTIME(date, '%Y') AS year, FROM_UNIXTIME(date, '%m') AS month, COUNT(*) AS count FROM tl_news WHERE pid IN(".implode(',', array_map('intval', $this->news_archives)).')'.(!System::getContainer()->get('contao.security.token_checker')->isPreviewMode() ? " AND (start='' OR start<='$time') AND (stop='' OR stop>'".($time + 60)."') AND published='1'" : '').(\count($newsIds) > 0 ? (' AND id IN ('.implode(',', $newsIds).')') : '').' GROUP BY year, month ORDER BY year DESC, month DESC');

        while ($objDates->next()) {
            $arrData[$objDates->year][$objDates->month] = $objDates->count;
        }

        // Sort the data
        foreach (array_keys($arrData) as $key) {
            'ascending' === $this->news_order ? ksort($arrData[$key]) : krsort($arrData[$key]);
        }

        ('ascending' === $this->news_order) ? ksort($arrData) : krsort($arrData);

        $arrItems = [];

        // Prepare the navigation
        foreach ($arrData as $intYear => $arrMonth) {
            $count = 0;
            $limit = \count($arrMonth);

            foreach ($arrMonth as $intMonth => $intCount) {
                $intDate = $intYear.$intMonth;
                $intMonth = (int) $intMonth - 1;

                $quantity = sprintf($intCount < 2 ? $GLOBALS['TL_LANG']['MSC']['entry'] : $GLOBALS['TL_LANG']['MSC']['entries'], $intCount);

                $arrItems[$intYear][$intMonth]['date'] = $intDate;
                $arrItems[$intYear][$intMonth]['link'] = $GLOBALS['TL_LANG']['MONTHS'][$intMonth].' '.$intYear;
                $arrItems[$intYear][$intMonth]['href'] = $this->strUrl.'?month='.$intDate;
                $arrItems[$intYear][$intMonth]['title'] = \StringUtil::specialchars($GLOBALS['TL_LANG']['MONTHS'][$intMonth].' '.$intYear.' ('.$quantity.')');
                $arrItems[$intYear][$intMonth]['class'] = trim((1 === ++$count ? 'first ' : '').($count === $limit ? 'last' : ''));
                $arrItems[$intYear][$intMonth]['isActive'] = \Input::get('month') === $intDate;
                $arrItems[$intYear][$intMonth]['quantity'] = $quantity;
            }
        }

        $this->Template->items = $arrItems;
        $this->Template->showQuantity = '' !== $this->news_showQuantity ? true : false;
        $this->Template->url = $this->strUrl.'?';
        $this->Template->activeYear = \Input::get('year');
    }

    /**
     * Generate the daily menu.
     */
    protected function compileDailyMenu(): void
    {
        $arrData = [];
        $time = \Date::floorToMinute();
        $newsIds = $this->getFilteredNewsIds();

        // Get the dates
        $objDates = $this->Database->query("SELECT FROM_UNIXTIME(date, '%Y%m%d') AS day, COUNT(*) AS count FROM tl_news WHERE pid IN(".implode(',', array_map('intval', $this->news_archives)).')'.(!System::getContainer()->get('contao.security.token_checker')->isPreviewMode() ? " AND (start='' OR start<='$time') AND (stop='' OR stop>'".($time + 60)."') AND published='1'" : '').(\count($newsIds) > 0 ? (' AND id IN ('.implode(',', $newsIds).')') : '').' GROUP BY day ORDER BY day DESC');

        while ($objDates->next()) {
            $arrData[$objDates->day] = $objDates->count;
        }

        // Sort the data
        krsort($arrData);

        // Create the date object
        try {
            $this->Date = \Input::get('day') ? new \Date(\Input::get('day'), 'Ymd') : new \Date();
        } catch (\OutOfBoundsException $e) {
            throw new PageNotFoundException('Page not found: '.\Environment::get('uri'));
        }

        $intYear = date('Y', $this->Date->tstamp);
        $intMonth = date('m', $this->Date->tstamp);

        $this->Template->intYear = $intYear;
        $this->Template->intMonth = $intMonth;

        // Previous month
        $prevMonth = 1 === $intMonth ? 12 : $intMonth - 1;
        $prevYear = 1 === $intMonth ? $intYear - 1 : $intYear;
        $lblPrevious = $GLOBALS['TL_LANG']['MONTHS'][$prevMonth - 1].' '.$prevYear;

        $this->Template->prevHref = $this->strUrl.'?day='.$prevYear.(\strlen($prevMonth) < 2 ? '0' : '').$prevMonth.'01';
        $this->Template->prevTitle = \StringUtil::specialchars($lblPrevious);
        $this->Template->prevLink = $GLOBALS['TL_LANG']['MSC']['news_previous'].' '.$lblPrevious;
        $this->Template->prevLabel = $GLOBALS['TL_LANG']['MSC']['news_previous'];

        // Current month
        $this->Template->current = $GLOBALS['TL_LANG']['MONTHS'][date('m', $this->Date->tstamp) - 1].' '.date('Y', $this->Date->tstamp);

        // Next month
        $nextMonth = 12 === $intMonth ? 1 : $intMonth + 1;
        $nextYear = 12 === $intMonth ? $intYear + 1 : $intYear;
        $lblNext = $GLOBALS['TL_LANG']['MONTHS'][$nextMonth - 1].' '.$nextYear;

        $this->Template->nextHref = $this->strUrl.'?day='.$nextYear.(\strlen($nextMonth) < 2 ? '0' : '').$nextMonth.'01';
        $this->Template->nextTitle = \StringUtil::specialchars($lblNext);
        $this->Template->nextLink = $lblNext.' '.$GLOBALS['TL_LANG']['MSC']['news_next'];
        $this->Template->nextLabel = $GLOBALS['TL_LANG']['MSC']['news_next'];

        // Set week start day
        if (!$this->news_startDay) {
            $this->news_startDay = 0;
        }

        $this->Template->daily = true;
        $this->Template->days = $this->compileDays();
        $this->Template->weeks = $this->compileWeeks($arrData);
        $this->Template->showQuantity = '' !== $this->news_showQuantity ? true : false;
    }

    /**
     * Generate the menu URL with category.
     *
     * @return string
     */
    protected function generateCategoryUrl()
    {
        /** @var PageModel $target */
        if ($this->jumpTo && ($target = $this->objModel->getRelated('jumpTo')) instanceof PageModel) {
            $page = $target;
        } else {
            $page = $GLOBALS['objPage'];
        }

        $manager = System::getContainer()->get('codefog_news_categories.manager');

        /** @var NewsCategoryModel $category */
        $category = NewsCategoryModel::findPublishedByIdOrAlias(Input::get($manager->getParameterName()));

        // Generate the category URL
        if (null !== $category) {
            $url = $manager->generateUrl($category, $page);
        } else {
            // Generate the regular URL
            $url = $page->getFrontendUrl();
        }

        return $url;
    }

    /**
     * Get the filtered news IDs.
     *
     * @return array
     *
     * @throws PageNotFoundException
     */
    protected function getFilteredNewsIds()
    {
        try {
            $criteria = System::getContainer()
                ->get('codefog_news_categories.news_criteria_builder')
                ->getCriteriaForMenuModule($this->news_archives, $this)
            ;
        } catch (CategoryNotFoundException $e) {
            throw new PageNotFoundException($e->getMessage());
        }

        if (null === $criteria) {
            return [];
        }

        $table = $criteria->getNewsModelAdapter()->getTable();

        return Database::getInstance()
            ->prepare("SELECT id FROM $table WHERE ".implode(' AND ', $criteria->getColumns()))
            ->execute($criteria->getValues())
            ->fetchEach('id')
        ;
    }
}
