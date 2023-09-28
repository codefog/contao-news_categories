<?php

declare(strict_types=1);

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\FrontendModule;

use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Contao\CoreBundle\Routing\ResponseContext\HtmlHeadBag\HtmlHeadBag;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContext;
use Contao\Database;
use Contao\FrontendTemplate;
use Contao\Input;
use Contao\System;

class NewsCategoriesModule extends NewsModule
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
        $param = $container->get('codefog_news_categories.manager')->getParameterName();

        // Get the active category
        if (null !== ($activeCategory = NewsCategoryModel::findPublishedByIdOrAlias(Input::get($param)))) {
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
            $this->navigationTpl = 'nav_newscategories';
        }

        $template = new FrontendTemplate($this->navigationTpl);
        $template->type = static::class;
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
                0 === \count($this->currentNewsCategories) && null === $this->activeCategory,
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
                !$this->showLevel || $this->showLevel >= $level ? $this->renderNewsCategories($category->id, $ids, $level) : '',
                $category,
            );
        }

        $template->items = $items;

        return $template->parse();
    }
}
