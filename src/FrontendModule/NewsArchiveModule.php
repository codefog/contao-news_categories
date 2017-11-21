<?php

namespace Codefog\NewsCategoriesBundle\FrontendModule;

use Codefog\NewsCategoriesBundle\Criteria;
use Codefog\NewsCategoriesBundle\Model\NewsModel;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\Model\Collection;
use Contao\ModuleNewsArchive;
use Contao\PageModel;
use Contao\System;

class NewsArchiveModule extends ModuleNewsArchive
{
    /**
	 * Generate the module
	 */
	protected function compile()
	{
		/** @var PageModel $objPage */
		global $objPage;

		$limit = null;
		$offset = 0;
		$intBegin = 0;
		$intEnd = 0;

		$intYear = \Input::get('year');
		$intMonth = \Input::get('month');
		$intDay = \Input::get('day');

		// Jump to the current period
		if (!isset($_GET['year']) && !isset($_GET['month']) && !isset($_GET['day']) && $this->news_jumpToCurrent != 'all_items')
		{
			switch ($this->news_format)
			{
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
		try
		{
			if ($intYear)
			{
				$strDate = $intYear;
				$objDate = new \Date($strDate, 'Y');
				$intBegin = $objDate->yearBegin;
				$intEnd = $objDate->yearEnd;
				$this->headline .= ' ' . date('Y', $objDate->tstamp);
			}
			elseif ($intMonth)
			{
				$strDate = $intMonth;
				$objDate = new \Date($strDate, 'Ym');
				$intBegin = $objDate->monthBegin;
				$intEnd = $objDate->monthEnd;
				$this->headline .= ' ' . \Date::parse('F Y', $objDate->tstamp);
			}
			elseif ($intDay)
			{
				$strDate = $intDay;
				$objDate = new \Date($strDate, 'Ymd');
				$intBegin = $objDate->dayBegin;
				$intEnd = $objDate->dayEnd;
				$this->headline .= ' ' . \Date::parse($objPage->dateFormat, $objDate->tstamp);
			}
			elseif ($this->news_jumpToCurrent == 'all_items')
			{
				$intBegin = 0;
				$intEnd = time();
			}
		}
		catch (\OutOfBoundsException $e)
		{
			throw new PageNotFoundException('Page not found: ' . \Environment::get('uri'));
		}

		$this->Template->articles = array();

		// Split the result
		if ($this->perPage > 0)
		{
			// Get the total number of items
			$intTotal = $this->countNewsItems($intBegin, $intEnd);

			if ($intTotal > 0)
			{
				$total = $intTotal;

				// Get the current page
				$id = 'page_a' . $this->id;
				$page = (\Input::get($id) !== null) ? \Input::get($id) : 1;

				// Do not index or cache the page if the page number is outside the range
				if ($page < 1 || $page > max(ceil($total/$this->perPage), 1))
				{
					throw new PageNotFoundException('Page not found: ' . \Environment::get('uri'));
				}

				// Set limit and offset
				$limit = $this->perPage;
				$offset = (max($page, 1) - 1) * $this->perPage;

				// Add the pagination menu
				$objPagination = new \Pagination($total, $this->perPage, \Config::get('maxPaginationLinks'), $id);
				$this->Template->pagination = $objPagination->generate("\n  ");
			}
		}

		// Get the news items
		if (isset($limit))
		{
		    $objArticles = $this->fetchNewsItems($intBegin, $intEnd, $limit, $offset);
		}
		else
		{
		    $objArticles = $this->fetchNewsItems($intBegin, $intEnd);
		}

		// Add the articles
		if ($objArticles !== null)
		{
			$this->Template->articles = $this->parseArticles($objArticles);
		}

		$this->Template->headline = trim($this->headline);
		$this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];
		$this->Template->empty = $GLOBALS['TL_LANG']['MSC']['empty'];
	}

    /**
     * Count the news items
     *
     * @param int $begin
     * @param int $end
     *
     * @return int
     */
    protected function countNewsItems($begin, $end)
    {
        if (($criteria = $this->getSearchCriteria($begin, $end)) === null) {
            return 0;
        }

        return NewsModel::countBy($criteria->getColumns(), $criteria->getValues());
	}

    /**
     * Fetch the news items
     *
     * @param int $begin
     * @param int $end
     * @param int $limit
     * @param int $offset
     *
     * @return Collection|null
     */
    protected function fetchNewsItems($begin, $end, $limit = null, $offset = null)
    {
        if (($criteria = $this->getSearchCriteria($begin, $end)) === null) {
            return null;
        }

        $criteria->setLimit($limit);
        $criteria->setOffset($offset);

        return NewsModel::findBy($criteria->getColumns(), $criteria->getValues(), $criteria->getOptions());
	}

    /**
     * Get the search criteria
     *
     * @param int $begin
     * @param int $end
     *
     * @return Criteria|null
     */
    protected function getSearchCriteria($begin, $end)
    {
        return System::getContainer()
            ->get('codefog_news_categories.search_builder')
            ->getCriteriaForArchiveModule($this->news_archives, $begin, $end, $this);
    }
}
