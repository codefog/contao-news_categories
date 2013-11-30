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
 * Front end module "news categories".
 */
class ModuleNewsCategories extends \ModuleNews
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_newscategories';


	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new \BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### NEWS CATEGORIES MENU ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		$this->news_archives = $this->sortOutProtected(deserialize($this->news_archives));

		// Return if there are no archives
		if (!is_array($this->news_archives) || empty($this->news_archives))
		{
			return '';
		}

		return parent::generate();
	}


	/**
	 * Generate the module
	 */
	protected function compile()
	{
		$objCategories = \NewsCategoryModel::findPublishedByParent($this->news_archives, ($this->news_customCategories ? deserialize($this->news_categories) : null));

		// Return if no categories are found
		if ($objCategories === null)
		{
			$this->Template->categories = array();
			return;
		}

		global $objPage;
		$strUrl = $this->generateFrontendUrl($objPage->row(), '/category/%s');

		// Get the jumpTo page
		if ($this->jumpTo > 0 && $objPage->id != $this->jumpTo)
		{
			$objJump = \PageModel::findByPk($this->jumpTo);

			if ($objJump !== null)
			{
				$strUrl = $this->generateFrontendUrl($objJump->row(), '/category/%s');
			}
		}

		$count = 0;
		$total = $objCategories->count();
		$arrCategories = array();

		// Add the reset categories link
		if ($this->news_resetCategories)
		{
			$arrCategories[] = array
			(
				'class' => 'reset first' . (($total == 1) ? ' last' : '') . ' even',
				'title' => specialchars($GLOBALS['TL_LANG']['MSC']['resetCategories'][1]),
				'href' => ampersand(str_replace('/category/%s', '', $strUrl)),
				'link' => $GLOBALS['TL_LANG']['MSC']['resetCategories'][0],
				'isActive' => !\Input::get('category') ? true : false
			);

			$count = 1;
			$total++;
		}

		// Generate the categories
		while ($objCategories->next())
		{
			$strTitle = $objCategories->frontendTitle ? $objCategories->frontendTitle : $objCategories->title;
			$arrCategories[$objCategories->id] = $objCategories->row();
			$arrCategories[$objCategories->id]['class'] = 'news_category_' . $objCategories->id . ((++$count == 1) ? ' first' : '') . (($count == $total) ? ' last' : '') . ((($count % 2) == 0) ? ' odd' : ' even');
			$arrCategories[$objCategories->id]['title'] = specialchars($strTitle);
			$arrCategories[$objCategories->id]['href'] = ampersand(sprintf($strUrl, ($GLOBALS['TL_CONFIG']['disableAlias'] ? $objCategories->id : $objCategories->alias)));
			$arrCategories[$objCategories->id]['link'] = $strTitle;
			$arrCategories[$objCategories->id]['isActive'] = ((\Input::get('category') != '') && (\Input::get('category') == ($GLOBALS['TL_CONFIG']['disableAlias'] ? $objCategories->id : $objCategories->alias))) ? true : false;
		}

		$this->Template->categories = $arrCategories;
	}
}
