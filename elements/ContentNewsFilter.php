<?php

/**
 * news_categories extension for Contao Open Source CMS
 *
 * Copyright (C) 2011-2014 Codefog
 *
 * @package news_categories
 * @author  Webcontext <http://webcontext.com>
 * @author  Codefog <info@codefog.pl>
 * @author  Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 * @license LGPL
 */

namespace NewsCategories;


/**
 * content element to filter new in newslist (module) or newsarchives (module)"
 */
class ContentNewsFilter extends \Contao\ContentModule
{

	/**
	 * Parse the template
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'FE' && !BE_USER_LOGGED_IN && ($this->invisible || ($this->start != '' && $this->start > time()) || ($this->stop != '' && $this->stop < time())))
		{
			return '';
		}

		$objModule = \ModuleModel::findByPk($this->module);

		if ($objModule === null || ('newslist' !== $objModule->type && 'newsarchive' !== $objModule->type))
		{
			return '';
		}

		$strClass = \Module::findClass($objModule->type);
		if (!class_exists($strClass))
		{
			return '';
		}

		$objModule->typePrefix = 'ce_';
		$objModule             = new $strClass($objModule, $this->strColumn);

		// Overwrite spacing and CSS ID
		$objModule->origSpace  = $objModule->space;
		$objModule->space      = $this->space;
		$objModule->origCssID  = $objModule->cssID;
		$objModule->cssID      = $this->cssID;

		// Override news filter settings
		$objModule->news_filterCategories = $this->news_filterCategories;
		$objModule->news_filterDefault    = $this->news_filterDefault;
		$objModule->news_filterPreserve   = $this->news_filterPreserve;

		return $objModule->generate();
	}

}
