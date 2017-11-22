<?php

namespace Codefog\NewsCategoriesBundle\FrontendModule;

use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Codefog\NewsCategoriesBundle\NewsCategory;
use Contao\Controller;
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
     * @var NewsCategory
     */
    protected $activeCategory = null;

    /**
     * News categories of the current news item
     * @var array
     */
    protected $currentNewsCategories = [];

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

        $this->currentNewsCategories = $this->getCurrentNewsCategories();

        return parent::generate();
    }

    /**
     * Generate the module
     */
    protected function compile()
    {
        $customCategories = $this->news_customCategories ? StringUtil::deserialize($this->news_categories, true) : [];

        // Get all categories whether they have news or not
        if ($this->news_showEmptyCategories) {
            if (count($customCategories) > 0) {
                $categories = NewsCategoryModel::findPublishedByIds($customCategories);
            } else {
                $categories = NewsCategoryModel::findPublished();
            }
        } else {
            // Get the categories that do have news assigned
            $categories = NewsCategoryModel::findPublishedByArchives($this->news_archives, $customCategories);
        }

        // Return if no categories are found
        if ($categories === null) {
            $this->Template->categories = '';

            return;
        }

        $param = System::getContainer()->get('codefog_news_categories.url_generator')->getParameterName();

        // Get the active category
        if (($activeCategory = NewsCategoryModel::findPublishedByIdOrAlias(Input::get($param))) !== null) {
            $this->activeCategory = new NewsCategory($activeCategory);
        }

        $ids = [];

        // Get the parent categories IDs
        /** @var NewsCategoryModel $category */
        foreach ($categories as $category) {
            $ids = array_merge($ids, Database::getInstance()->getParentRecords($category->id, $category->getTable()));
        }

        $this->Template->categories = $this->renderNewsCategories((int) $this->news_categoriesRoot, array_unique($ids));
    }

    /**
     * Get the target page
     *
     * @return PageModel
     */
    protected function getTargetPage()
    {
        static $page;

        if ($page === null) {
            if ($this->jumpTo > 0
                && (int) $GLOBALS['objPage']->id !== (int) $this->jumpTo
                && ($target = PageModel::findPublishedById($this->jumpTo)) !== null
            ) {
                $page = $target;
            } else {
                $page = $GLOBALS['objPage'];
            }
        }

        return $page;
    }

    /**
     * Get the category IDs of the current news item
     *
     * @return array
     */
    protected function getCurrentNewsCategories()
    {
        if (!($alias = Input::getAutoItem('items'))
            || ($news = NewsModel::findPublishedByParentAndIdOrAlias($alias, $this->news_archives)) === null
        ) {
            return [];
        }

        $ids = Model::getRelatedValues('tl_news', 'categories', $news->id);
        $ids = array_map('intval', array_unique($ids));

        return $ids;
    }

    /**
     * Recursively compile the news categories and return it as HTML string
     *
     * @param integer $pid
     * @param array   $ids
     * @param integer $level
     *
     * @return string
     */
    protected function renderNewsCategories($pid, array $ids, $level = 1)
    {
        $categoryModels = NewsCategoryModel::findPublishedByIds($ids, $pid);

        if ($categoryModels === null) {
            return '';
        }

        $urlGenerator = System::getContainer()->get('codefog_news_categories.url_generator');

        // Layout template fallback
        if (!$this->navigationTpl) {
            $this->navigationTpl = 'nav_newscategories';
        }

        $template = new FrontendTemplate($this->navigationTpl);
        $template->type = get_class($this);
        $template->cssID = $this->cssID;
        $template->level = 'level_' . $level;
        $template->showQuantity = $this->news_showQuantity;

        $categories = [];

        // Add the "reset categories" link
        if ($this->news_resetCategories && $level === 1) {
            $categories[] = $this->generateItem(
                $this->getTargetPage()->getFrontendUrl(),
                $GLOBALS['TL_LANG']['MSC']['resetCategories'][0],
                $GLOBALS['TL_LANG']['MSC']['resetCategories'][1],
                'reset',
                count($this->currentNewsCategories) === 0 && $this->activeCategory === null
            );
        }

        $level++;

        /** @var NewsCategoryModel $categoryModel */
        foreach ($categoryModels as $categoryModel) {
            $category = new NewsCategory($categoryModel);

            // Generate the category individual URL or the filter-link
            if ($this->news_forceCategoryUrl && ($targetPage = $category->getTargetPage()) !== null) {
                $url = $targetPage->getFrontendUrl();
            } else {
                $url = $urlGenerator->generateUrl($category, $this->getTargetPage());
            }

            $categories[] = $this->generateItem(
                $url,
                $category->getTitle(),
                $category->getTitle(),
                $this->generateItemCssClass($category),
                $this->activeCategory !== null && (int) $this->activeCategory->getModel()->id === (int) $categoryModel->id,
                $this->renderNewsCategories($categoryModel->id, $ids, $level),
                $category
            );
        }

        // Add first/last/even/odd classes
        RowClass::withKey('class')->addFirstLast()->addEvenOdd()->applyTo($categories);

        $template->items = $categories;

        return $template->parse();
    }

    /**
     * Generate the item
     *
     * @param string            $url
     * @param string            $link
     * @param string            $title
     * @param string            $cssClass
     * @param bool              $isActive
     * @param string            $subitems
     * @param NewsCategory|null $category
     *
     * @return array
     */
    protected function generateItem($url, $link, $title, $cssClass, $isActive, $subitems = '', NewsCategory $category = null)
    {
        $data = [];

        // Set the data from category
        if ($category !== null) {
            $data = $category->getModel()->row();
        }

        $data['isActive'] = $isActive;
        $data['subitems'] = $subitems;
        $data['class'] = $cssClass;
        $data['title'] = StringUtil::specialchars($title);
        $data['linkTitle'] = StringUtil::specialchars($title);
        $data['link'] = $link;
        $data['href'] = ampersand($url);
        $data['quantity'] = 0;

        // Add the "active" class
        if ($isActive) {
            $data['class'] = trim($data['class'] . ' active');
        }

        // Add the "submenu" class
        if ($subitems) {
            $data['class'] = trim($data['class'] . ' submenu');
        }

        // Add the news quantity
        if ($this->news_showQuantity) {
            $data['quantity'] = ($category === null) ? NewsCategoryModel::getUsage($this->news_archives) : $category->getUsage($this->news_archives);
        }

        // Add the image
        if ($category !== null && ($image = $category->getImage()) !== null) {
            $data['image'] = new \stdClass();
            Controller::addImageToTemplate($data['image'], ['singleSRC' => $image->path, 'size' => $this->news_categoryImgSize]);
        } else {
            $data['image'] = null;
        }

        return $data;
    }

    /**
     * Generate the item CSS class
     *
     * @param NewsCategory $category
     *
     * @return string
     */
    protected function generateItemCssClass(NewsCategory $category)
    {
        $cssClasses = [$category->getCssClass()];

        // Add the trail class
        if (in_array((int) $category->getModel()->id, $category->getTrailIds(), true)) {
            $cssClasses[] = 'trail';
        }

        // Add the news trail class
        if (in_array((int) $category->getModel()->id, $this->currentNewsCategories, true)) {
            $cssClasses[] = 'news_trail';
        }

        return implode(' ', $cssClasses);
    }
}
