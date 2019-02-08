<?php

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\FrontendModule;

use Codefog\NewsCategoriesBundle\Criteria\NewsCriteria;
use Codefog\NewsCategoriesBundle\Exception\NoNewsException;
use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Codefog\NewsCategoriesBundle\NewsCategoriesManager;
use Contao\BackendTemplate;
use Contao\Controller;
use Contao\Database;
use Contao\FrontendTemplate;
use Contao\Model\Collection;
use Contao\ModuleNews;
use Contao\NewsModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Haste\Generator\RowClass;
use Haste\Input\Input;
use Haste\Model\Model;
use Patchwork\Utf8;

class CumulativeFilterModule extends ModuleNews
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'mod_newscategories_cumulative';

    /**
     * Active categories.
     *
     * @var Collection|null
     */
    protected $activeCategories;

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

            $template->wildcard = '### '.Utf8::strtoupper($GLOBALS['TL_LANG']['FMD'][$this->type][0]).' ###';
            $template->title = $this->headline;
            $template->id = $this->id;
            $template->link = $this->name;
            $template->href = 'contao/?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

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
     * Get the URL category separator character
     *
     * @return string
     */
    public static function getCategorySeparator()
    {
        return '__';
    }

    /**
     * Generate the module.
     */
    protected function compile()
    {
        $rootCategoryId = (int) $this->news_categoriesRoot;

        // Set the custom categories either by root ID or by manual selection
        if ($this->news_customCategories) {
            $customCategories = StringUtil::deserialize($this->news_categories, true);
        } else {
            $subcategories = NewsCategoryModel::findPublishedByPid($rootCategoryId);
            $customCategories = ($subcategories !== null) ? $subcategories->fetchEach('id') : [];
        }

        // Get the subcategories of custom categories
        if (\count($customCategories) > 0 && $this->news_includeSubcategories) {
            $customCategories = NewsCategoryModel::getAllSubcategoriesIds($customCategories);
        }

        // First, fetch the active categories
        $this->activeCategories = $this->getActiveCategories($customCategories);

        // Then, fetch the inactive categories
        $inactiveCategories = $this->getInactiveCategories($customCategories);

        // Generate active categories
        if ($this->activeCategories !== null) {
            $this->Template->activeCategories = $this->renderNewsCategories($rootCategoryId, $this->activeCategories->fetchEach('id'), true);

            // Add the canonical URL tag
            if ($this->news_enableCanonicalUrls) {
                $GLOBALS['TL_HEAD'][] = sprintf('<link rel="canonical" href="%s">', $GLOBALS['objPage']->getAbsoluteUrl());
            }
        } else {
            $this->Template->activeCategories = '';
        }

        // Generate inactive categories
        if ($inactiveCategories !== null) {
            $this->Template->inactiveCategories = $this->renderNewsCategories($rootCategoryId, $inactiveCategories->fetchEach('id'));
        } else {
            $this->Template->inactiveCategories = '';
        }
    }

    /**
     * Get the active categories
     *
     * @param array $customCategories
     *
     * @return Collection|null
     */
    protected function getActiveCategories(array $customCategories = [])
    {
        $param = System::getContainer()->get('codefog_news_categories.manager')->getParameterName();

        if (!($aliases = Input::get($param))) {
            return null;
        }

        $aliases = StringUtil::trimsplit(static::getCategorySeparator(), $aliases);
        $aliases = array_unique(array_filter($aliases));

        if (count($aliases) === 0) {
            return null;
        }

        // Get the categories that do have news assigned
        $models = NewsCategoryModel::findPublishedByArchives($this->news_archives, $customCategories, $aliases);

        // No models have been found but there are some aliases present
        if ($models === null && count($aliases) !== 0) {
            Controller::redirect($this->getTargetPage()->getFrontendUrl());
        }

        // Validate the provided aliases with the categories found
        if ($models !== null) {
            $realAliases = [];

            /** @var NewsCategoryModel $model */
            foreach ($models as $model) {
                $realAliases[] = $this->manager->getCategoryAlias($model, $GLOBALS['objPage']);
            }

            if (count(array_diff($aliases, $realAliases)) > 0) {
                Controller::redirect($this->getTargetPage()->getFrontendUrl(sprintf(
                    '/%s/%s',
                    $this->manager->getParameterName($GLOBALS['objPage']->rootId),
                    implode(static::getCategorySeparator(), $realAliases)
                )));
            }
        }

        return $models;
    }

    /**
     * Get the inactive categories
     *
     * @param array $customCategories
     *
     * @return Collection|null
     */
    protected function getInactiveCategories(array $customCategories = [])
    {
        // Find only the categories that still can display some results combined with active categories
        if ($this->activeCategories !== null) {
            $columns = [];
            $values = [];

            // Collect the news that match all active categories
            /** @var NewsCategoryModel $activeCategory */
            foreach ($this->activeCategories as $activeCategory) {
                $criteria = new NewsCriteria(System::getContainer()->get('contao.framework'));

                try {
                    $criteria->setBasicCriteria($this->news_archives);
                    $criteria->setCategory($activeCategory->id, false, (bool) $this->news_includeSubcategories);
                } catch (NoNewsException $e) {
                    continue;
                }

                $columns = array_merge($columns, $criteria->getColumns());
                $values = array_merge($values, $criteria->getValues());
            }

            // Should not happen but you never know
            if (count($columns) === 0) {
                return null;
            }

            $newsIds = Database::getInstance()
                ->prepare('SELECT id FROM tl_news WHERE ' . implode(' AND ', $columns))
                ->execute($values)
                ->fetchEach('id')
            ;

            if (count($newsIds) === 0) {
                return null;
            }

            $categoryIds = Model::getRelatedValues('tl_news', 'categories', $newsIds);
            $categoryIds = \array_map('intval', $categoryIds);
            $categoryIds = \array_unique(\array_filter($categoryIds));

            // Include the parent categories
            if ($this->news_includeSubcategories) {
                foreach ($categoryIds as $categoryId) {
                    $categoryIds = array_merge($categoryIds, Database::getInstance()->getParentRecords($categoryId, 'tl_news_category'));
                }
            }

            // Remove the active categories, so they are not considered again
            $categoryIds = array_diff($categoryIds, $this->activeCategories->fetchEach('id'));

            // Filter by custom categories
            if (count($customCategories) > 0) {
                $categoryIds = array_intersect($categoryIds, $customCategories);
            }

            if (count($categoryIds) === 0) {
                return null;
            }

            $customCategories = $categoryIds;
        }

        return NewsCategoryModel::findPublishedByArchives($this->news_archives, $customCategories);
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
        if (!($alias = Input::getAutoItem('items', false, true))
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
     * @param bool  $isActiveCategories
     *
     * @return string
     */
    protected function renderNewsCategories($pid, array $ids, $isActiveCategories = false)
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
        $template->level = 'level_1';
        $template->showQuantity = $isActiveCategories ? false : (bool) $this->news_showQuantity;
        $template->isActiveCategories = $isActiveCategories;

        $items = [];
        $activeAliases = [];
        $resetUrl = $this->getTargetPage()->getFrontendUrl();

        // Collect the active category parameters
        if ($this->activeCategories !== null) {
            /** @var NewsCategoryModel $activeCategory */
            foreach ($this->activeCategories as $activeCategory) {
                $activeAliases[] = $this->manager->getCategoryAlias($activeCategory, $GLOBALS['objPage']);
            }
        }

        // Add the "reset categories" link
        if ($isActiveCategories && $this->news_resetCategories && count($activeAliases) > 0) {
            $items[] = $this->generateItem(
                $resetUrl,
                $GLOBALS['TL_LANG']['MSC']['resetCategoriesCumulative'][0],
                $GLOBALS['TL_LANG']['MSC']['resetCategoriesCumulative'][1],
                'reset',
                0 === \count($this->currentNewsCategories) && null === $this->activeCategories
            );
        }

        $parameterName = $this->manager->getParameterName($GLOBALS['objPage']->rootId);

        /** @var NewsCategoryModel $category */
        foreach ($categories as $category) {
            $categoryAlias = $this->manager->getCategoryAlias($category, $GLOBALS['objPage']);

            // Add/remove the category alias to the active ones
            if (in_array($categoryAlias, $activeAliases, true)) {
                $aliases = array_diff($activeAliases, [$categoryAlias]);
            } else {
                $aliases = array_merge($activeAliases, [$categoryAlias]);
            }

            // Generate the category URL if there are any aliases to add, otherwise use the reset URL
            if (count($aliases) > 0) {
                $url = $this->getTargetPage()->getFrontendUrl(sprintf('/%s/%s', $parameterName, implode(static::getCategorySeparator(), $aliases)));
            } else {
                $url = $resetUrl;
            }

            $items[] = $this->generateItem(
                $url,
                $category->getTitle(),
                $category->getTitle(),
                $this->generateItemCssClass($category),
                in_array($categoryAlias, $activeAliases, true),
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
     * @param NewsCategoryModel|null $category
     *
     * @return array
     */
    protected function generateItem($url, $link, $title, $cssClass, $isActive, NewsCategoryModel $category = null)
    {
        $data = [];

        // Set the data from category
        if (null !== $category) {
            $data = $category->row();
        }

        $data['isActive'] = $isActive;
        $data['subitems'] = '';
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

        // Add the news quantity
        if ($this->news_showQuantity) {
            if (null === $category) {
                $data['quantity'] = NewsCategoryModel::getUsage($this->news_archives);
            } else {
                $data['quantity'] = NewsCategoryModel::getUsage(
                    $this->news_archives,
                    $category->id,
                    (bool) $this->news_includeSubcategories,
                    ($this->activeCategories !== null) ? $this->activeCategories->fetchEach('id') : []
                );
            }
        }

        // Add the image
        if (null !== $category && null !== ($image = $this->manager->getImage($category))) {
            $data['image'] = new \stdClass();
            Controller::addImageToTemplate($data['image'], [
                'singleSRC' => $image->path,
                'size' => $this->news_categoryImgSize,
                'alt' => $title,
                'imageTitle' => $title,
            ]);
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
