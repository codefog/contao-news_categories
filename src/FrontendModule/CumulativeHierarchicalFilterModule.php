<?php

declare(strict_types=1);

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2023, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\FrontendModule;

use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Codefog\NewsCategoriesBundle\NewsCategoriesManager;
use Contao\CoreBundle\Routing\ResponseContext\HtmlHeadBag\HtmlHeadBag;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContext;
use Contao\Database;
use Contao\FrontendTemplate;
use Contao\Input;
use Contao\System;

class CumulativeHierarchicalFilterModule extends NewsModule
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'mod_newscategories';

    /**
     * Generate the module.
     */
    protected function compile(): void
    {
        $categories = $this->getCategories();

        // Return if no categories are found
        if (null === $categories) {
            $this->Template->categories = '';

            return;
        }

        $container = System::getContainer();
        $param = $container->get(NewsCategoriesManager::class)->getParameterName();

        // Get the active category
        if (($alias = Input::get($param)) && null !== ($activeCategory = NewsCategoryModel::findPublishedByIdOrAlias($alias))) {
            $this->activeCategory = $activeCategory;

            // Set the canonical URL
            if ($this->news_enableCanonicalUrls && ($responseContext = $container->get('contao.routing.response_context_accessor')->getResponseContext())) {
                /** @var ResponseContext $responseContext */
                if ($responseContext->has(HtmlHeadBag::class)) {
                    /** @var HtmlHeadBag $htmlHeadBag */
                    $htmlHeadBag = $responseContext->get(HtmlHeadBag::class);
                    $htmlHeadBag->setCanonicalUri($GLOBALS['objPage']->getAbsoluteUrl());
                }
            }
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
     * Recursively compile the news categories and return it as HTML string.
     *
     * @param int $pid
     * @param int $level
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
            $this->navigationTpl = 'nav_newscategories_hierarchical';
        }

        $template = new FrontendTemplate($this->navigationTpl);
        $template->type = static::class;
        $template->cssID = $this->cssID;
        $template->level = 'level_'.$level;
        $template->showQuantity = $this->news_showQuantity;

        $items = [];
        $activeCategories = $this->getActiveCategories($ids);

        // Add the "reset categories" link
        if ($this->news_resetCategories && 1 === $level) {
            $items[] = $this->generateItem(
                $this->getTargetPage()->getFrontendUrl(),
                $GLOBALS['TL_LANG']['MSC']['resetCategories'][0],
                $GLOBALS['TL_LANG']['MSC']['resetCategories'][1],
                'reset',
                null === $activeCategories || 0 === \count($activeCategories),
            );
        }

        $activeAliases = [];

        // Collect the active category parameters
        if (null !== $activeCategories) {
            /** @var NewsCategoryModel $activeCategory */
            foreach ($activeCategories as $activeCategory) {
                $activeAliases[] = $activeCategory->getAlias($GLOBALS['TL_LANGUAGE']);
            }
        }

        $pageUrl = $this->getTargetPage()->getFrontendUrl(sprintf('/%s', $this->manager->getParameterName($GLOBALS['objPage']->rootId)).'/%s');
        $resetUrl = $this->getTargetPage()->getFrontendUrl();

        /** @var NewsCategoryModel $category */
        foreach ($categories as $category) {
            // Generate the category individual URL or the filter-link
            $categoryAlias = $category->getAlias($GLOBALS['TL_LANGUAGE']);

            // Add/remove the category alias to the active ones
            if (\in_array($categoryAlias, $activeAliases, true)) {
                $aliases = array_diff($activeAliases, [$categoryAlias]);
            } else {
                $aliases = [...$activeAliases, $categoryAlias];
            }

            // Get the URL
            if (\count($aliases) > 0) {
                $url = sprintf($pageUrl, implode(static::getCategorySeparator(), $aliases));
            } else {
                $url = $resetUrl;
            }

            ++$level;

            $items[] = $this->generateItem(
                $url,
                $category->getTitle(),
                $category->getTitle(),
                $this->generateItemCssClass($category),
                null !== $activeCategories && \in_array($category, $activeCategories->getModels(), true),
                !$this->showLevel || $this->showLevel >= $level ? $this->renderNewsCategories($category->id, $ids, $level) : '',
                $category,
            );
        }

        $template->items = $items;

        return $template->parse();
    }
}
