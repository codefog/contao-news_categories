<?php

namespace Codefog\NewsCategoriesBundle\FrontendModule;

use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Codefog\NewsCategoriesBundle\NewsCategory;
use Contao\Database;
use Contao\FrontendTemplate;
use Contao\ModuleNews;
use Contao\BackendTemplate;
use Contao\NewsModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Haste\Generator\RowClass;
use Haste\Input\Input;
use Haste\Model\Model;
use Patchwork\Utf8;

class NewsCategoriesModule extends ModuleNews
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
    protected $activeCategory = null;

    /**
     * Active news categories
     * @var array
     */
    protected $activeNewsCategories = [];

    /**
     * Category trail
     * @var array
     */
    protected $categoriesTrail = [];

    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (TL_MODE === 'BE') {
            $template = new BackendTemplate('be_wildcard');

            $template->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['newscategories'][0]) . ' ###';
            $template->title = $this->headline;
            $template->id = $this->id;
            $template->link = $this->name;
            $template->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $template->parse();
        }

        $this->news_archives = $this->sortOutProtected(StringUtil::deserialize($this->news_archives, true));

        // Return if there are no archives
        if (count($this->news_archives) === 0) {
            return '';
        }

        // Get the category IDs of the active news item
        if (($alias = Input::getAutoItem('items'))
            && ($news = NewsModel::findPublishedByParentAndIdOrAlias($alias, $this->news_archives)) === null
        ) {
            $this->activeNewsCategories = Model::getRelatedValues('tl_news', 'categories', $news->id);
            $this->activeNewsCategories = array_map('intval', array_unique($this->activeNewsCategories));
        }

        return parent::generate();
    }

    /**
     * Generate the module
     */
    protected function compile()
    {
        $categories = NewsCategoryModel::findPublishedByParent(
            $this->news_archives,
            $this->news_customCategories ? deserialize($this->news_categories, true) : []
        );

        // Return if no categories are found
        if ($categories === null) {
            $this->Template->categories = '';

            return;
        }

        $param = System::getContainer()->get('codefog_news_categories.url_generator')->getParameterName();
        $url = $GLOBALS['objPage']->getFrontendUrl('/' . $param . '/%s');

        // Get the target page
        if ($this->jumpTo > 0
            && (int) $GLOBALS['objPage']->id !== (int) $this->jumpTo
            && ($target = PageModel::findPublishedById($this->jumpTo)) !== null
        ) {
            $url = $target->getFrontendUrl('/' . $param . '/%s');
        }

        $ids = [];

        // Get the parent categories IDs
        /** @var NewsCategoryModel $category */
        foreach ($categories as $category) {
            $ids = array_merge($ids, Database::getInstance()->getParentRecords($category->id, 'tl_news_category'));
        }

        // Get the active category
        if (($alias = Input::get($param))
            && ($this->activeCategory = NewsCategoryModel::findPublishedByIdOrAlias($alias)) !== null
        ) {
            $this->categoriesTrail = Database::getInstance()->getParentRecords($this->activeCategory->id, 'tl_news_category');
            $this->categoriesTrail = array_map('intval', $this->categoriesTrail);

            // Remove the current category from the trail
            unset($this->categoriesTrail[array_search($this->activeCategory->id, $this->categoriesTrail)]);
        }

        $rootId = 0;

        // Set the custom root ID
        if ($this->news_categoriesRoot) {
            $rootId = $this->news_categoriesRoot;
        }

        $this->Template->categories = $this->renderNewsCategories($rootId, array_unique($ids), $url);
    }

    /**
     * Recursively compile the news categories and return it as HTML string
     *
     * @param integer $pid
     * @param array   $ids
     * @param string  $url
     * @param integer $level
     *
     * @return string
     */
    protected function renderNewsCategories($pid, array $ids, $url, $level = 1)
    {
        $categoryModels = NewsCategoryModel::findPublishedByPidAndIds($pid, $ids);

        if ($categoryModels === null) {
            return '';
        }

        $param = System::getContainer()->get('codefog_news_categories.url_generator')->getParameterName();
        $categories = [];

        // Layout template fallback
        if (!$this->navigationTpl) {
            $this->navigationTpl = 'nav_newscategories';
        }

        $template = new FrontendTemplate($this->navigationTpl);
        $template->type = get_class($this);
        $template->cssID = $this->cssID;
        $template->level = 'level_' . $level;
        $template->showQuantity = $this->news_showQuantity;

        // Add the "reset categories" link
        if ($this->news_resetCategories && $level === 1) {
            $newsQuantity = 0;

            // Get the news quantity
            if ($this->news_showQuantity) {
                // @todo – add the missing method
                $newsQuantity = \NewsModel::countPublishedByCategoryAndPids($this->news_archives);
            }

            $isActive = Input::get($param) ? false : true;

            $categories[] = [
                'isActive' => count($this->activeNewsCategories) > 0 && $isActive,
                'subitems' => '',
                'class' => 'reset' . ($isActive ? ' active' : ''),
                'title' => StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['resetCategories'][1]),
                'linkTitle' => StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['resetCategories'][1]),
                'link' => $GLOBALS['TL_LANG']['MSC']['resetCategories'][0],
                'href' => ampersand(str_replace('/' . $param . '/%s', '', $url)),
                'quantity' => $newsQuantity,
            ];
        }

        $level++;

        // Generate categories
        /** @var NewsCategoryModel $categoryModel */
        foreach ($categoryModels as $categoryModel) {
            $subcategories = '';

            // Get the subcategories
            if ($categoryModel->subcategories) {
                $subcategories = $this->renderNewsCategories($categoryModel->id, $ids, $url, $level);
            }

            $category = new NewsCategory($categoryModel);
            $isActive = $this->activeCategory !== null && (int) $this->activeCategory->id === (int) $categoryModel->id;
            $cssClasses = [$category->getCssClass()];

            // Add category active class
            if ($isActive) {
                $cssClasses[] = 'active';
            }

            // Add the subcategory class
            if ($subcategories) {
                $cssClasses[] = 'submenu';
            }

            // Add the trail class
            if (in_array((int) $categoryModel->id, $this->categoriesTrail, true)) {
                $cssClasses[] = 'trail';
            }

            // Add the news trail class
            if (in_array((int) $categoryModel->id, $this->activeNewsCategories, true)) {
                $cssClasses[] = 'news_trail';
            }

            $row = $categoryModel->row();
            $row['isActive'] = $isActive;
            $row['subitems'] = $subcategories;
            $row['class'] = implode(' ', $cssClasses);
            $row['title'] = StringUtil::specialchars($category->getTitle(), true);
            $row['linkTitle'] = StringUtil::specialchars($category->getTitle(), true);
            $row['link'] = $category->getTitle();
            $row['href'] = ampersand(urlencode(sprintf(urldecode($url), $categoryModel->alias)));
            $row['quantity'] = 0;

            // Get the news quantity
            if ($this->news_showQuantity) {
                // @todo – add the missing method
                $row['quantity'] = \NewsModel::countPublishedByCategoryAndPids($this->news_archives, $categoryModel->id);
            }

            $categories[] = $row;
        }

        // Add first/last/even/odd classes
        RowClass::withKey('class')->addFirstLast()->addEvenOdd()->applyTo($categories);

        $template->items = $categories;

        return $template->parse();
    }
}
