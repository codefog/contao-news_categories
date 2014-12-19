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
 * Override the default content element "content module".
 */
class ContentNewsModuleFilter extends \Contao\ContentModule
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

		if ($objModule === null)
		{
			return '';
		}
		
		// Check for compatible module		
		if ('newslist' !== $objModule->type || 'newsarchive' !== $objModule->type) {
			return parent::generate();
		}

		$strClass = \Module::findClass($objModule->type);
		if (!class_exists($strClass))
		{
			return '';
		}

		$objModule->typePrefix = 'ce_';
		$objModule = new $strClass($objModule, $this->strColumn);

		// Overwrite spacing and CSS ID
		$objModule->origSpace  = $objModule->space;
		$objModule->space      = $this->space;
		$objModule->origCssID  = $objModule->cssID;
		$objModule->cssID      = $this->cssID;
        
        $GLOBALS['NEWS_FILTER_CATEGORIES'] = $this->news_filterCategories ? true : false;
        $GLOBALS['NEWS_FILTER_DEFAULT']    = deserialize($this->news_filterDefault, true);
        $GLOBALS['NEWS_FILTER_PRESERVE']   = $this->news_filterPreserve;
		
		// Set flag that categories are already set
		$objModule->categoriesSetByContentElement = true;		

		return $objModule->generate();
	}

}
