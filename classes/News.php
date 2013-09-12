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
 * Provide methods regarding news archives
 */
class News extends \Contao\News
{

	/**
	 * Add the categories to the template
	 * @param object
	 * @param array
	 */
	public function addCategoriesToTemplate($objTemplate, $arrData)
	{
		if (isset($arrData['categories']))
		{
			$arrCategories = array();
			$arrCategoriesList = array();
			$categories = deserialize($arrData['categories']);

			if (is_array($categories) && !empty($categories))
			{
				$objCategories = \NewsCategoryModel::findPublishedByIds($categories);

				// Add the categories to template
				if ($objCategories !== null)
				{
					while ($objCategories->next())
					{
						$arrCategories[$objCategories->id] = $objCategories->row();
						$arrCategoriesList[$objCategories->id] = $objCategories->title;
					}
				}
			}

			$objTemplate->categories = $arrCategories;
			$objTemplate->categoriesList = $arrCategoriesList;
		}
	}


	/**
	 * Set the news categories, if any
	 * @param array
	 */
	protected function generateFiles($arrFeed)
	{
		$arrCategories = deserialize($arrFeed['categories']);

		if (is_array($arrCategories) && !empty($arrCategories))
		{
			$GLOBALS['NEWS_FILTER_CATEGORIES'] = true;
			$GLOBALS['NEWS_FILTER_DEFAULT'] = $arrCategories;
		}
		else
		{
			$GLOBALS['NEWS_FILTER_CATEGORIES'] = false;
		}

		parent::generateFiles($arrFeed);
	}
}
