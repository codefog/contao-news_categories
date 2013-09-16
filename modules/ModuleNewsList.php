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
 * Override the default front end module "news list".
 */
class ModuleNewsList extends \Contao\ModuleNewsList
{

	/**
	 * Set the flag to filter news by categories
	 * @return string
	 */
	public function generate()
	{
		$GLOBALS['NEWS_FILTER_CATEGORIES'] = $this->news_filterCategories ? true : false;
		$GLOBALS['NEWS_FILTER_DEFAULT'] = deserialize($this->news_filterDefault, true);

		return parent::generate();
	}
}
