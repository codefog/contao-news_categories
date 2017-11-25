<?php

/*
 * News Categories Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\FrontendModule;

use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Codefog\NewsCategoriesBundle\NewsCategoriesManager;
use Contao\BackendTemplate;
use Contao\Controller;
use Contao\Database;
use Contao\FrontendTemplate;
use Contao\ModuleNews;
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
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'mod_newscategories';

    /**
     * Active category.
     *
     * @var NewsCategoryModel
     */
    protected $activeCategory = null;

    /**
     * News categories of the current news item.
     *
     * @var array
     */
    protected $currentNewsCategories = [];

    /**
     * @var NewsCategoriesManager
     */
    protected $manager;

    /**
     * Display a wildcard in the back end.
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE === 'BE') {
            $template = new BackendTemplate('be_wildcard');

            $template->wildcard = '### '.Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['newscategories'][0]).' ###';
            $template->title = $this->headline;
            $template->id = $this->id;
            $template->link = $this->name;
            $template->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

            return $template->parse();
        }

        $this->news_archives = $this->sortOutProtected(StringUtil::deserialize($this->news_archives, true));

        // Return if there are no archives
        if (0 === \count($this->news_archives)) {
            return '';
        }

        $this->manager = System::getContainer()->get('codefog_news_categories.manager');
        $this->currentNewsCategories = $this->getCurrentNewsCategories();

        return parent::generate();
    }

    /**
     * Generate the module.
     */
    protected function compile()
    {
        $customCategories = $this->news_customCategories ? StringUtil::deserialize($this->news_categories, true) : [];

        // Get all categories whether they have news or not
        if ($this->news_showEmptyCategories) {
            if (\count($customCategories) > 0) {
                $categories = NewsCategoryModel::findPublishedByIds($customCategories);
            } else {
                $categories = NewsCategoryModel::findPublished();
            }
        } else {
            // Get the categories that do have news assigned
            $categories = NewsCategoryModel::findPublishedByArchives($this->news_archives, $customCategories);
        }

        // Return if no categories are found
        if (null === $categories) {
            $this->Template->categories = '';

            return;
        }

        $param = System::getContainer()->get('codefog_news_categories.manager')->getParameterName();

        // Get the active category
        if (null !== ($activeCategory = NewsCategoryModel::findPublishedByIdOrAlias(Input::get($param)))) {
            $this->activeCategory = $activeCategory;
        }

        $ids = [];

        // Get the parent categories IDs
        /** @var NewsCategoryModel $category */
        foreach ($categories as $category) {
            $ids = \array_merge($ids, Database::getInstance()->getParentRecords($category->id, $category->getTable()));
        }

        $this->Template->categories = $this->renderNewsCategories((int) $this->news_categoriesRoot, \array_unique($ids));
    }

    /**
     * Get the target page.
     *
     * @return PageModel
     */
    protected function getTargetPage()
    {
        static $page;

        if (null === $page) {
            if ($this->jumpTo > 0
                && (int) $GLOBALS['objPage']->id !== (int) $this->jumpTo
                && null !== ($target = PageModel::findPublishedById($this->jumpTo))
            ) {
                $page = $target;
            } else {
                $page = $GLOBALS['objPage'];
            }
        }

        return $page;
    }

    /**
     * Get the category IDs of the current news item.
     *
     * @return array
     */
    protected function getCurrentNewsCategories()
    {
        if (!($alias = Input::getAutoItem('items'))
            || null === ($news = NewsModel::findPublishedByParentAndIdOrAlias($alias, $this->news_archives))
        ) {
            return [];
        }

        $ids = Model::getRelatedValues('tl_news', 'categories', $news->id);
        $ids = \array_map('intval', \array_unique($ids));

        return $ids;
    }

    /**
     * Recursively compile the news categories and return it as HTML string.
     *
     * @param int   $pid
     * @param array $ids
     * @param int   $level
     *
     * @return string
     */
    protected function renderNewsCategories($pid, array $ids, $level = 1)
    {
        if (null === ($categories = NewsCategoryModel::findPublishedByIds($ids, $pid))) {
            return '';
        }

        // Layout template fallback
        if (!$this->navigationTpl) {
            $this->navigationTpl = 'nav_newscategories';
        }

        $template = new FrontendTemplate($this->navigationTpl);
        $template->type = \get_class($this);
        $template->cssID = $this->cssID;
        $template->level = 'level_'.$level;
        $template->showQuantity = $this->news_showQuantity;

        $items = [];

        // Add the "reset categories" link
        if ($this->news_resetCategories && 1 === $level) {
            $items[] = $this->generateItem(
                $this->getTargetPage()->getFrontendUrl(),
                $GLOBALS['TL_LANG']['MSC']['resetCategories'][0],
                $GLOBALS['TL_LANG']['MSC']['resetCategories'][1],
                'reset',
                0 === \count($this->currentNewsCategories) && null === $this->activeCategory
            );
        }

        ++$level;

        /** @var NewsCategoryModel $category */
        foreach ($categories as $category) {
            // Generate the category individual URL or the filter-link
            if ($this->news_forceCategoryUrl && null !== ($targetPage = $this->manager->getTargetPage($category))) {
                $url = $targetPage->getFrontendUrl();
            } else {
                $url = $this->manager->generateUrl($category, $this->getTargetPage());
            }

            $items[] = $this->generateItem(
                $url,
                $category->getTitle(),
                $category->getTitle(),
                $this->generateItemCssClass($category),
                null !== $this->activeCategory && (int) $this->activeCategory->id === (int) $category->id,
                $this->renderNewsCategories($category->id, $ids, $level),
                $category
            );
        }

        // Add first/last/even/odd classes
        RowClass::withKey('class')->addFirstLast()->addEvenOdd()->applyTo($items);

        $template->items = $items;

        return $template->parse();
    }

    /**
     * Generate the item.
     *
     * @param string                 $url
     * @param string                 $link
     * @param string                 $title
     * @param string                 $cssClass
     * @param bool                   $isActive
     * @param string                 $subitems
     * @param NewsCategoryModel|null $category
     *
     * @return array
     */
    protected function generateItem($url, $link, $title, $cssClass, $isActive, $subitems = '', NewsCategoryModel $category = null)
    {
        $data = [];

        // Set the data from category
        if (null !== $category) {
            $data = $category->row();
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
            $data['class'] = \trim($data['class'].' active');
        }

        // Add the "submenu" class
        if ($subitems) {
            $data['class'] = \trim($data['class'].' submenu');
        }

        // Add the news quantity
        if ($this->news_showQuantity) {
            if (null === $category) {
                $data['quantity'] = NewsCategoryModel::getUsage($this->news_archives);
            } else {
                $data['quantity'] = NewsCategoryModel::getUsage($this->news_archives, $category->id, (bool) $this->news_includeSubcategories);
            }
        }

        // Add the image
        if (null !== $category && null !== ($image = $this->manager->getImage($category))) {
            $data['image'] = new \stdClass();
            Controller::addImageToTemplate($data['image'], ['singleSRC' => $image->path, 'size' => $this->news_categoryImgSize]);
        } else {
            $data['image'] = null;
        }

        return $data;
    }

    /**
     * Generate the item CSS class.
     *
     * @param NewsCategoryModel $category
     *
     * @return string
     */
    protected function generateItemCssClass(NewsCategoryModel $category)
    {
        $cssClasses = [$category->getCssClass()];

        // Add the trail class
        if (\in_array((int) $category->id, $this->manager->getTrailIds($category), true)) {
            $cssClasses[] = 'trail';
        }

        // Add the news trail class
        if (\in_array((int) $category->id, $this->currentNewsCategories, true)) {
            $cssClasses[] = 'news_trail';
        }

        return \implode(' ', $cssClasses);
    }
}
