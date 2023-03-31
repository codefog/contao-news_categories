<?php

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\FrontendModule;

use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Contao\FrontendTemplate;
use Contao\StringUtil;
use Contao\System;

class CumulativeFilterModule extends NewsModule
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'mod_newscategories_cumulative';

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
            $customCategories = (null !== $subcategories) ? $subcategories->fetchEach('id') : [];
        }

        // Get the subcategories of custom categories
        if (\count($customCategories) > 0 && $this->news_includeSubcategories) {
            $customCategories = NewsCategoryModel::getAllSubcategoriesIds($customCategories);
        }

        // First, fetch the active categories
        $this->activeCategories = $this->getActiveCategories($customCategories);

        // Then, fetch the inactive categories
        $inactiveCategories = $this->getInactiveCategories($customCategories);

        $this->Template->resetUrl = null;

        // Generate active categories
        if (null !== $this->activeCategories) {
            $this->Template->activeCategories = $this->renderNewsCategories($rootCategoryId, $this->activeCategories->fetchEach('id'), true);

            // Add the "reset categories" link
            if ($this->news_resetCategories) {
                $this->Template->resetUrl = $this->getTargetPage()->getFrontendUrl();
            }
        } else {
            $this->Template->activeCategories = '';
        }

        // Generate inactive categories
        if (null !== $inactiveCategories) {
            $this->Template->inactiveCategories = $this->renderNewsCategories($rootCategoryId, $inactiveCategories->fetchEach('id'));
        } else {
            $this->Template->inactiveCategories = '';
        }
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

        // Collect the active category parameters
        if (null !== $this->activeCategories) {
            /** @var NewsCategoryModel $activeCategory */
            foreach ($this->activeCategories as $activeCategory) {
                $activeAliases[] = $this->manager->getCategoryAlias($activeCategory, $GLOBALS['objPage']);
            }
        }

        $resetUrl = $this->getTargetPage()->getFrontendUrl();
        $pageUrl = $this->getTargetPage()->getFrontendUrl(sprintf('/%s', $this->manager->getParameterName($GLOBALS['objPage']->rootId)) . '/%s');

        /** @var NewsCategoryModel $category */
        foreach ($categories as $category) {
            $categoryAlias = $this->manager->getCategoryAlias($category, $GLOBALS['objPage']);

            // Add/remove the category alias to the active ones
            if (\in_array($categoryAlias, $activeAliases, true)) {
                $aliases = \array_diff($activeAliases, [$categoryAlias]);
            } else {
                $aliases = \array_merge($activeAliases, [$categoryAlias]);
            }

            // Generate the category URL if there are any aliases to add, otherwise use the reset URL
            if (\count($aliases) > 0) {
                $url = sprintf($pageUrl, \implode(static::getCategorySeparator(), $aliases));
            } else {
                $url = $resetUrl;
            }

            $items[] = $this->generateItem(
                $url,
                $category->getTitle(),
                $category->getTitle(),
                $this->generateItemCssClass($category),
                \in_array($categoryAlias, $activeAliases, true),
                '',
                $category
            );
        }

        $template->items = $items;

        return $template->parse();
    }
}
