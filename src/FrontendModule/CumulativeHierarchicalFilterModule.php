<?php


namespace Codefog\NewsCategoriesBundle\FrontendModule;


use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Contao\Database;
use Contao\FrontendTemplate;
use Contao\System;
use Haste\Generator\RowClass;
use Haste\Input\Input;

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
    protected function compile()
    {
        $categories = $this->getCategories();

        // Return if no categories are found
        if (null === $categories) {
            $this->Template->categories = '';

            return;
        }

        $param = System::getContainer()->get('codefog_news_categories.manager')->getParameterName();

        // Get the active category
        if (null !== ($activeCategory = NewsCategoryModel::findPublishedByIdOrAlias(Input::get($param)))) {
            $this->activeCategory = $activeCategory;

            // Add the canonical URL tag
            // TODO: to be dropped when deps require Contao 4.13+
            if ($this->news_enableCanonicalUrls && !System::getContainer()->has('contao.routing.response_context_accessor')) {
                $GLOBALS['TL_HEAD'][] = sprintf('<link rel="canonical" href="%s">', $GLOBALS['objPage']->getAbsoluteUrl());
            }
        }

        $ids = [];

        // Get the parent categories IDs
        /** @var NewsCategoryModel $category */
        foreach ($categories as $category) {
            $ids = array_merge($ids, Database::getInstance()->getParentRecords($category->id, $category->getTable()));
        }

        $this->Template->categories = $this->renderNewsCategories((int)$this->news_categoriesRoot, array_unique($ids));
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
            $this->navigationTpl = 'nav_newscategories_hierarchical';
        }

        $template = new FrontendTemplate($this->navigationTpl);
        $template->type = get_class($this);
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
                null === $activeCategories || 0 === count($activeCategories)
            );
        }

        $activeAliases = [];

        // Collect the active category parameters
        if ($activeCategories !== null) {
            /** @var NewsCategoryModel $activeCategory */
            foreach ($activeCategories as $activeCategory) {
                $activeAliases[] = $this->manager->getCategoryAlias($activeCategory, $GLOBALS['objPage']);
            }
        }

        $pageUrl = $this->getTargetPage()->getFrontendUrl(sprintf('/%s', $this->manager->getParameterName($GLOBALS['objPage']->rootId)) . '/%s');
        $resetUrl = $this->getTargetPage()->getFrontendUrl();

        /** @var NewsCategoryModel $category */
        foreach ($categories as $category) {
            // Generate the category individual URL or the filter-link
            $categoryAlias = $this->manager->getCategoryAlias($category, $GLOBALS['objPage']);

            // Add/remove the category alias to the active ones
            if (in_array($categoryAlias, $activeAliases, true)) {
                $aliases = array_diff($activeAliases, [$categoryAlias]);
            } else {
                $aliases = array_merge($activeAliases, [$categoryAlias]);
            }

            // Get the URL
            if (count($aliases) > 0) {
                $url = sprintf($pageUrl, implode(static::getCategorySeparator(), $aliases));
            } else {
                $url = $resetUrl;
            }

            $level++;

            $items[] = $this->generateItem(
                $url,
                $category->getTitle(),
                $category->getTitle(),
                $this->generateItemCssClass($category),
                $activeCategories !== null && in_array($category, $activeCategories->getModels()),
                (!$this->showLevel || $this->showLevel >= $level) ? $this->renderNewsCategories($category->id, $ids, $level) : '',
                $category
            );
        }

        // Add first/last/even/odd classes
        RowClass::withKey('class')->addFirstLast()->addEvenOdd()->applyTo($items);

        $template->items = $items;

        return $template->parse();
    }
}
