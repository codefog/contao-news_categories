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

use Codefog\NewsCategoriesBundle\Criteria\NewsCriteria;
use Codefog\NewsCategoriesBundle\Criteria\NewsCriteriaBuilder;
use Codefog\NewsCategoriesBundle\Exception\CategoryNotFoundException;
use Contao\Config;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\Date;
use Contao\Environment;
use Contao\Input;
use Contao\Model\Collection;
use Contao\ModuleNewsArchive;
use Contao\NewsModel;
use Contao\PageModel;
use Contao\Pagination;
use Contao\System;
use Contao\Template;

/**
 * @property Template $Template
 */
class NewsArchiveModule extends ModuleNewsArchive
{
    protected function compile(): void
    {
        /** @var PageModel $objPage */
        global $objPage;

        $limit = null;
        $offset = 0;
        $intBegin = 0;
        $intEnd = 0;

        $intYear = (int) Input::get('year');
        $intMonth = (int) Input::get('month');
        $intDay = (int) Input::get('day');

        // Jump to the current period
        if (!isset($_GET['year']) && !isset($_GET['month']) && !isset($_GET['day']) && 'all_items' !== $this->news_jumpToCurrent) {
            switch ($this->news_format) {
                case 'news_year':
                    $intYear = date('Y');
                    break;

                default:
                case 'news_month':
                    $intMonth = date('Ym');
                    break;

                case 'news_day':
                    $intDay = date('Ymd');
                    break;
            }
        }

        // Create the date object
        try {
            if ($intYear) {
                $intDate = $intYear;
                $objDate = new Date($intDate, 'Y');
                $intBegin = $objDate->yearBegin;
                $intEnd = $objDate->yearEnd;
                $this->headline .= ' '.date('Y', $objDate->tstamp);
            } elseif ($intMonth) {
                $intDate = $intMonth;
                $objDate = new Date($intDate, 'Ym');
                $intBegin = $objDate->monthBegin;
                $intEnd = $objDate->monthEnd;
                $this->headline .= ' '.Date::parse('F Y', $objDate->tstamp);
            } elseif ($intDay) {
                $intDate = $intDay;
                $objDate = new Date($intDate, 'Ymd');
                $intBegin = $objDate->dayBegin;
                $intEnd = $objDate->dayEnd;
                $this->headline .= ' '.Date::parse($objPage->dateFormat, $objDate->tstamp);
            } elseif ('all_items' === $this->news_jumpToCurrent) {
                $intBegin = 0;
                $intEnd = time();
            }
        } catch (\OutOfBoundsException) {
            throw new PageNotFoundException('Page not found: '.Environment::get('uri'));
        }

        $this->Template->articles = [];

        // Split the result
        if ($this->perPage > 0) {
            // Get the total number of items
            $intTotal = $this->countNewsItems($intBegin, $intEnd);

            if ($intTotal > 0) {
                $total = $intTotal;

                // Get the current page
                $id = 'page_a'.$this->id;
                $page = (int) (Input::get($id) ?? 1);

                // Do not index or cache the page if the page number is outside the range
                if ($page < 1 || $page > max(ceil($total / $this->perPage), 1)) {
                    throw new PageNotFoundException('Page not found: '.Environment::get('uri'));
                }

                // Set limit and offset
                $limit = (int) $this->perPage;
                $offset = (max($page, 1) - 1) * $this->perPage;

                // Add the pagination menu
                $objPagination = new Pagination($total, $this->perPage, Config::get('maxPaginationLinks'), $id);
                $this->Template->pagination = $objPagination->generate("\n  ");
            }
        }

        // Get the news items
        if (isset($limit)) {
            $objArticles = $this->fetchNewsItems($intBegin, $intEnd, $limit, $offset);
        } else {
            $objArticles = $this->fetchNewsItems($intBegin, $intEnd);
        }

        // Add the articles
        if (null !== $objArticles) {
            $this->Template->articles = $this->parseArticles($objArticles);
        }

        $this->Template->headline = trim($this->headline);
        $this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];
        $this->Template->empty = $GLOBALS['TL_LANG']['MSC']['empty'];
    }

    /**
     * Count the news items.
     */
    protected function countNewsItems(int $begin, int $end): int
    {
        if (null === ($criteria = $this->getSearchCriteria($begin, $end))) {
            return 0;
        }

        return NewsModel::countBy($criteria->getColumns(), $criteria->getValues());
    }

    /**
     * Fetch the news items.
     *
     * @return Collection<NewsModel>|null
     */
    protected function fetchNewsItems(int $begin, int $end, int|null $limit = null, int|null $offset = null): Collection|null
    {
        if (null === ($criteria = $this->getSearchCriteria($begin, $end))) {
            return null;
        }

        $criteria->setLimit($limit);
        $criteria->setOffset($offset);

        return NewsModel::findBy($criteria->getColumns(), $criteria->getValues(), $criteria->getOptions());
    }

    /**
     * Get the search criteria.
     *
     * @throws PageNotFoundException
     */
    protected function getSearchCriteria(int $begin, int $end): NewsCriteria|null
    {
        try {
            $criteria = System::getContainer()
                ->get(NewsCriteriaBuilder::class)
                ->getCriteriaForArchiveModule($this->news_archives, $begin, $end, $this)
            ;
        } catch (CategoryNotFoundException $e) {
            throw new PageNotFoundException($e->getMessage(), 0, $e);
        }

        return $criteria;
    }
}
