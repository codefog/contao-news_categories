<?php

/**
 * news_categories extension for Contao Open Source CMS
 *
 * Copyright (C) 2013 Codefog Ltd
 *
 * @package news_categories
 * @link    http://codefog.pl
 * @author  Webcontext <http://webcontext.com>
 * @author  Codefog Ltd <info@codefog.pl>
 * @author  Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 * @license LGPL
 */

namespace NewsCategories;


/**
 * Reads and writes news categories
 */
class NewsCategoryModel extends \Model
{

	/**
	 * Table name
	 * @var string
	 */
	protected static $strTable = 'tl_news_category';


	/**
	 * Find published news categories by their archives
	 *
	 * @param array $arrPids An array of archives
	 *
	 * @return \Model|null The NewsModelCategpry or null if there are no categories
	 */
	public static function findPublishedByParent($arrPids)
	{
		if (!is_array($arrPids) || empty($arrPids))
		{
			return null;
		}

		$time = time();
		$t = static::$strTable;
		$arrColumns = array("$t.id IN (SELECT category_id FROM tl_news_categories WHERE news_id IN (SELECT id FROM tl_news WHERE pid IN (" . implode(',', array_map('intval', $arrPids)) . ")" . (!BE_USER_LOGGED_IN ? " AND (tl_news.start='' OR tl_news.start<$time) AND (tl_news.stop='' OR tl_news.stop>$time) AND tl_news.published=1" : "") . "))");

		if (!BE_USER_LOGGED_IN)
		{
			$arrColumns[] = "$t.published=1";
		}

		return static::findBy($arrColumns, null);
	}


	/**
	 * Find published category by ID or alias
	 *
	 * @param mixed $varId The numeric ID or alias name
	 *
	 * @return \Model|null The NewsCategoryModel or null if there is no category
	 */
	public static function findPublishedByIdOrAlias($varId)
	{
		$t = static::$strTable;
		$arrColumns = array("($t.id=? OR $t.alias=?)");

		if (!BE_USER_LOGGED_IN)
		{
			$time = time();
			$arrColumns[] = "$t.published=1";
		}

		return static::findBy($arrColumns, array((is_numeric($varId) ? $varId : 0), $varId));
	}


	/**
	 * Find published categories by IDs
	 *
	 * @param array $arrIds An array of category IDs
	 *
	 * @return \Model|null The NewsCategoryModel or null if there is no category
	 */
	public static function findPublishedByIds($arrIds)
	{
		if (!is_array($arrIds) || empty($arrIds))
		{
			return null;
		}

		$t = static::$strTable;
		$arrColumns = array("$t.id IN (" . implode(',', array_map('intval', $arrIds)) . ")");

		if (!BE_USER_LOGGED_IN)
		{
			$arrColumns[] = "$t.published=1";
		}

		return static::findBy($arrColumns, null);
	}
}
