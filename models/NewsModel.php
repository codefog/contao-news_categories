<?php

/**
 * news_categories extension for Contao Open Source CMS
 * 
 * Copyright (C) 2013 Codefog
 * 
 * @package news_categories
 * @link    http://codefog.pl
 * @author  Webcontext <http://webcontext.com>
 * @author  Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 * @license LGPL
 */


/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace NewsCategories;


/**
 * Reads and writes news and their categories
 */
class NewsModel extends \Contao\NewsModel
{

	/**
	 * Get the categories cache and return it as array
	 * @return array
	 */
	public static function getCategoriesCache()
	{
		static $arrCache;

		if (!is_array($arrCache))
		{
			$arrCache = array();
			$objCategories = \Database::getInstance()->execute("SELECT * FROM tl_news_categories");

			while ($objCategories->next())
			{
				$arrCache[$objCategories->category_id][] = $objCategories->news_id;
			}
		}

		return $arrCache;
	}


	/**
	 * Find published news items by their parent ID
	 * 
	 * @param array   $arrPids     An array of news archive IDs
	 * @param boolean $blnFeatured If true, return only featured news, if false, return only unfeatured news
	 * @param integer $intLimit    An optional limit
	 * @param integer $intOffset   An optional offset
	 * @param array   $arrOptions  An optional options array
	 * 
	 * @return \Model\Collection|null A collection of models or null if there are no news
	 */
	public static function findPublishedByPids($arrPids, $blnFeatured=null, $intLimit=0, $intOffset=0, array $arrOptions=array())
	{
		if (!is_array($arrPids) || empty($arrPids))
		{
			return null;
		}

		$t = static::$strTable;
		$arrColumns = array("$t.pid IN(" . implode(',', array_map('intval', $arrPids)) . ")");

		if ($blnFeatured === true)
		{
			$arrColumns[] = "$t.featured=1";
		}
		elseif ($blnFeatured === false)
		{
			$arrColumns[] = "$t.featured=''";
		}

		if (!BE_USER_LOGGED_IN)
		{
			$time = time();
			$arrColumns[] = "($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.published=1";
		}

		// Use the default filter
		if (is_array($GLOBALS['NEWS_FILTER_DEFAULT']) && !empty($GLOBALS['NEWS_FILTER_DEFAULT']))
		{
			$arrCategories = static::getCategoriesCache();

			if (!empty($arrCategories))
			{
				$arrIds = array();

				// Get the news IDs for particular categories
				foreach ($GLOBALS['NEWS_FILTER_DEFAULT'] as $intCategory)
				{
					$arrIds = array_merge($arrCategories[$intCategory], $arrIds);
				}

				$arrColumns['category'] = "$t.id IN (" . implode(',', array_unique($arrIds)) . ")";
			}
		}

		// Try to find by category
		if ($GLOBALS['NEWS_FILTER_CATEGORIES'] && \Input::get('category'))
		{
			$objCategory = \NewsCategoryModel::findPublishedByIdOrAlias(\Input::get('category'));

			if ($objCategory === null)
			{
				return null;
			}

			$arrCategories = static::getCategoriesCache();

			if (!empty($arrCategories))
			{
				$arrColumns['category'] = "$t.id IN (" . implode(',', $arrCategories[$objCategory->id]) . ")";
			}
		}

		$arrOptions = array
		(
			'order'  => "$t.date DESC",
			'limit'  => $intLimit,
			'offset' => $intOffset
		);

		return static::findBy($arrColumns, null, $arrOptions);
	}


	/**
	 * Count published news items by their parent ID
	 * 
	 * @param array   $arrPids     An array of news archive IDs
	 * @param boolean $blnFeatured If true, return only featured news, if false, return only unfeatured news
	 * @param array   $arrOptions  An optional options array
	 * 
	 * @return integer The number of news items
	 */
	public static function countPublishedByPids($arrPids, $blnFeatured=null, array $arrOptions=array())
	{
		if (!is_array($arrPids) || empty($arrPids))
		{
			return 0;
		}

		$t = static::$strTable;
		$arrColumns = array("$t.pid IN(" . implode(',', array_map('intval', $arrPids)) . ")");

		if ($blnFeatured === true)
		{
			$arrColumns[] = "$t.featured=1";
		}
		elseif ($blnFeatured === false)
		{
			$arrColumns[] = "$t.featured=''";
		}

		if (!BE_USER_LOGGED_IN)
		{
			$time = time();
			$arrColumns[] = "($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.published=1";
		}

		// Use the default filter
		if (is_array($GLOBALS['NEWS_FILTER_DEFAULT']) && !empty($GLOBALS['NEWS_FILTER_DEFAULT']))
		{
			$arrCategories = static::getCategoriesCache();

			if (!empty($arrCategories))
			{
				$arrIds = array();

				// Get the news IDs for particular categories
				foreach ($GLOBALS['NEWS_FILTER_DEFAULT'] as $category)
				{
					$arrIds = array_merge($arrCategories[$category], $arrIds);
				}

				$arrColumns['category'] = "$t.id IN (" . implode(',', array_unique($arrIds)) . ")";
			}
		}

		// Try to find by category
		if ($GLOBALS['NEWS_FILTER_CATEGORIES'] && \Input::get('category'))
		{
			$objCategory = \NewsCategoryModel::findPublishedByIdOrAlias(\Input::get('category'));

			if ($objCategory === null)
			{
				return null;
			}

			$arrCategories = static::getCategoriesCache();

			if (!empty($arrCategories))
			{
				$arrColumns['category'] = "$t.id IN (" . implode(',', $arrCategories[$objCategory->id]) . ")";
			}
		}

		return static::countBy($arrColumns, null);
	}
}
