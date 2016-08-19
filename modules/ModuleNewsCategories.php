<?php

/**
 * news_categories extension for Contao Open Source CMS
 *
 * Copyright (C) 2011-2014 Codefog
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
     * Active category
     * @var object
     */
    protected $objActiveCategory = null;

    /**
     * Active news categories
     * @var array
     */
    protected $activeNewsCategories = array();

    /**
     * Category trail
     * @var array
     */
    protected $arrCategoryTrail = array();

    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
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
        if (!is_array($this->news_archives) || empty($this->news_archives)) {
            return '';
        }

        $param = 'items';

        // Use the auto_item parameter if enabled
        if (!isset($_GET['items']) && $GLOBALS['TL_CONFIG']['useAutoItem'] && isset($_GET['auto_item'])) {
            $param = 'auto_item';
        }

        $newsModel = \NewsModel::findPublishedByParentAndIdOrAlias(\Input::get($param), $this->news_archives);

        // Get the category IDs of the active news item
        if ($newsModel !== null) {
            $this->activeNewsCategories = deserialize($newsModel->categories, true);
        }

        return parent::generate();
    }

    /**
     * Generate the module
     */
    protected function compile()
    {
        $strClass = \NewsCategories\NewsCategories::getModelClass();
        $objCategories = $strClass::findPublishedByParent($this->news_archives, ($this->news_customCategories ? deserialize($this->news_categories) : null));

        // Return if no categories are found
        if ($objCategories === null) {
            $this->Template->categories = '';

            return;
        }

        global $objPage;
        $strParam = NewsCategories::getParameterName();
        $strUrl = $this->generateFrontendUrl($objPage->row(), '/' . $strParam . '/%s');

        // Get the jumpTo page
        if ($this->jumpTo > 0 && $objPage->id != $this->jumpTo) {
            $objJump = \PageModel::findByPk($this->jumpTo);

            if ($objJump !== null) {
                $strUrl = $this->generateFrontendUrl($objJump->row(), '/' . $strParam . '/%s');
            }
        }

        $arrIds = array();

        // Get the parent categories IDs
        while ($objCategories->next()) {
            $arrIds = array_merge($arrIds, $this->Database->getParentRecords($objCategories->id, 'tl_news_category'));
        }

        // Get the active category
        if (\Input::get($strParam) != '') {
            $this->objActiveCategory = $strClass::findPublishedByIdOrAlias(\Input::get($strParam));

            if ($this->objActiveCategory !== null) {
                $this->arrCategoryTrail = $this->Database->getParentRecords($this->objActiveCategory->id, 'tl_news_category');

                // Remove the current category from the trail
                unset($this->arrCategoryTrail[array_search($this->objActiveCategory->id, $this->arrCategoryTrail)]);
            }
        }

        $rootId = 0;

        // Set the custom root ID
        if ($this->news_categoriesRoot) {
            $rootId = $this->news_categoriesRoot;
        }

        $this->Template->categories = $this->renderNewsCategories($rootId, array_unique($arrIds), $strUrl);
    }

    /**
     * Recursively compile the news categories and return it as HTML string
     * @param integer
     * @param integer
     * @return string
     */
    protected function renderNewsCategories($intPid, $arrIds, $strUrl, $intLevel=1)
    {
        $strClass = \NewsCategories\NewsCategories::getModelClass();
        $objCategories = $strClass::findPublishedByPidAndIds($intPid, $arrIds);

        if ($objCategories === null) {
            return '';
        }

        $strParam = NewsCategories::getParameterName();
        $arrCategories = array();

        // Layout template fallback
        if ($this->navigationTpl == '') {
            $this->navigationTpl = 'nav_newscategories';
        }

        $objTemplate = new \FrontendTemplate($this->navigationTpl);
        $objTemplate->type = get_class($this);
        $objTemplate->cssID = $this->cssID;
        $objTemplate->level = 'level_' . $intLevel;
        $objTemplate->showQuantity = $this->news_showQuantity;

        $count = 0;
        $total = $objCategories->count();

        // Add the "reset categories" link
        if ($this->news_resetCategories && $intLevel == 1) {
            $intNewsQuantity = 0;

            // Get the news quantity
            if ($this->news_showQuantity) {
                $intNewsQuantity = \NewsModel::countPublishedByCategoryAndPids($this->news_archives);
            }

            $blnActive = \Input::get($strParam) ? false : true;

            $arrCategories[] = array
            (
                'isActive' => empty($this->activeNewsCategories) && $blnActive,
                'subitems' => '',
                'class' => 'reset first' . (($total == 1) ? ' last' : '') . ' even' . ($blnActive ? ' active' : ''),
                'title' => specialchars($GLOBALS['TL_LANG']['MSC']['resetCategories'][1]),
                'linkTitle' => specialchars($GLOBALS['TL_LANG']['MSC']['resetCategories'][1]),
                'link' => $GLOBALS['TL_LANG']['MSC']['resetCategories'][0],
                'href' => ampersand(str_replace('/' . $strParam . '/%s', '', $strUrl)),
                'quantity' => $intNewsQuantity
            );

            $count = 1;
            $total++;
        }

        $intLevel++;

        // Render categories
        while ($objCategories->next()) {
            $strSubcategories = '';

            // Get the subcategories
            if ($objCategories->subcategories) {
                $strSubcategories = $this->renderNewsCategories($objCategories->id, $arrIds, $strUrl, $intLevel);
            }

            $blnActive = ($this->objActiveCategory !== null) && ($this->objActiveCategory->id == $objCategories->id);
            $strClass = ('news_category_' . $objCategories->id) . ($objCategories->cssClass ? (' ' . $objCategories->cssClass) : '') . ((++$count == 1) ? ' first' : '') . (($count == $total) ? ' last' : '') . ((($count % 2) == 0) ? ' odd' : ' even') . ($blnActive ? ' active' : '') . (($strSubcategories != '') ? ' submenu' : '') . (in_array($objCategories->id, $this->arrCategoryTrail) ? ' trail' : '') . (in_array($objCategories->id, $this->activeNewsCategories) ? ' news_trail' : '');
            $strTitle = $objCategories->frontendTitle ?: $objCategories->title;

            $arrRow = $objCategories->row();
            $arrRow['isActive'] = $blnActive;
            $arrRow['subitems'] = $strSubcategories;
            $arrRow['class'] = $strClass;
            $arrRow['title'] = specialchars($strTitle, true);
            $arrRow['linkTitle'] = specialchars($strTitle, true);
            $arrRow['link'] = $strTitle;
            $arrRow['href'] = ampersand(sprintf($strUrl, ($GLOBALS['TL_CONFIG']['disableAlias'] ? $objCategories->id : $objCategories->alias)));
            $arrRow['quantity'] = 0;

            // Get the news quantity
            if ($this->news_showQuantity) {
                $arrRow['quantity'] = \NewsModel::countPublishedByCategoryAndPids($this->news_archives, $objCategories->id);
            }

            $arrCategories[] = $arrRow;
        }

        $objTemplate->items = $arrCategories;

        return !empty($arrCategories) ? $objTemplate->parse() : '';
    }
}
