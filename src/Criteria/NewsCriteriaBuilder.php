<?php

declare(strict_types=1);

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\Criteria;

use Codefog\HasteBundle\Model\DcaRelationsModel;
use Codefog\NewsCategoriesBundle\Exception\CategoryNotFoundException;
use Codefog\NewsCategoriesBundle\Exception\NoNewsException;
use Codefog\NewsCategoriesBundle\FrontendModule\CumulativeFilterModule;
use Codefog\NewsCategoriesBundle\FrontendModule\NewsListModule;
use Codefog\NewsCategoriesBundle\FrontendModule\NewsModule;
use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Codefog\NewsCategoriesBundle\NewsCategoriesManager;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Input;
use Contao\Module;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
class NewsCriteriaBuilder
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly TokenChecker $tokenChecker,
        private readonly Connection $db,
        private readonly NewsCategoriesManager $manager,
    ) {
    }

    public function create(array $archives): NewsCriteria
    {
        $criteria = new NewsCriteria($this->framework, $this->tokenChecker);
        $criteria->setBasicCriteria($archives);

        return $criteria;
    }

    /**
     * Get the criteria for archive module.
     *
     * @param NewsModule $module
     */
    public function getCriteriaForArchiveModule(array $archives, int $begin, int $end, Module $module): NewsCriteria|null
    {
        $criteria = new NewsCriteria($this->framework, $this->tokenChecker);

        try {
            $criteria->setBasicCriteria($archives, $module->news_order);

            // Set the time frame
            $criteria->setTimeFrame($begin, $end);

            // Set the regular list criteria
            $this->setRegularListCriteria($criteria, $module);
        } catch (NoNewsException) {
            return null;
        }

        return $criteria;
    }

    /**
     * Get the criteria for list module.
     */
    public function getCriteriaForListModule(array $archives, bool|null $featured, Module $module): NewsCriteria|null
    {
        $criteria = new NewsCriteria($this->framework, $this->tokenChecker);

        try {
            $criteria->setBasicCriteria($archives, $module->news_order, $module->news_featured);

            // Set the featured filter
            if (null !== $featured) {
                $criteria->setFeatured($featured);
            }

            // Set the criteria for related categories
            if ($module instanceof NewsListModule && $module->news_relatedCategories) {
                $this->setRelatedListCriteria($criteria, $module);
            } else {
                // Set the regular list criteria
                $this->setRegularListCriteria($criteria, $module);
            }
        } catch (NoNewsException) {
            return null;
        }

        return $criteria;
    }

    /**
     * Get the criteria for menu module.
     *
     * @param NewsModule $module
     */
    public function getCriteriaForMenuModule(array $archives, Module $module): NewsCriteria|null
    {
        $criteria = new NewsCriteria($this->framework, $this->tokenChecker);

        try {
            $criteria->setBasicCriteria($archives, $module->news_order);

            // Set the regular list criteria
            $this->setRegularListCriteria($criteria, $module);
        } catch (NoNewsException) {
            return null;
        }

        return $criteria;
    }

    /**
     * Set the regular list criteria.
     *
     * @throws CategoryNotFoundException
     * @throws NoNewsException
     */
    private function setRegularListCriteria(NewsCriteria $criteria, Module $module): void
    {
        // Filter by default categories
        if (!empty($default = StringUtil::deserialize($module->news_filterDefault, true))) {
            $criteria->setDefaultCategories($default);
        }

        // Filter by multiple active categories
        if ($module->news_filterCategoriesCumulative) {
            /** @var Input $input */
            $input = $this->framework->getAdapter(Input::class);
            $param = $this->manager->getParameterName();

            if ($aliases = $input->get($param)) {
                $aliases = StringUtil::trimsplit(CumulativeFilterModule::getCategorySeparator(), $aliases);
                $aliases = array_unique(array_filter($aliases));

                if (\count($aliases) > 0) {
                    /** @var NewsCategoryModel $model */
                    $model = $this->framework->getAdapter(NewsCategoryModel::class);
                    $categories = [];

                    foreach ($aliases as $alias) {
                        // Return null if the category does not exist
                        if (null === ($category = $model->findPublishedByIdOrAlias($alias))) {
                            throw new CategoryNotFoundException(sprintf('News category "%s" was not found', $alias));
                        }

                        $categories[] = (int) $category->id;
                    }

                    if ($module->news_filterCategoriesUnion) {
                        $criteria->setCategories($categories, (bool) $module->news_filterPreserve, (bool) $module->news_includeSubcategories);
                    } else {
                        // Intersection filtering
                        foreach ($categories as $category) {
                            $criteria->setCategory($category, (bool) $module->news_filterPreserve, (bool) $module->news_includeSubcategories);
                        }
                    }
                }
            }

            return;
        }

        // Filter by active category
        if ($module->news_filterCategories) {
            /** @var Input $input */
            $input = $this->framework->getAdapter(Input::class);
            $param = $this->manager->getParameterName();

            if ($alias = $input->get($param)) {
                /** @var NewsCategoryModel $model */
                $model = $this->framework->getAdapter(NewsCategoryModel::class);

                // Return null if the category does not exist
                if (null === ($category = $model->findPublishedByIdOrAlias($alias))) {
                    throw new CategoryNotFoundException(sprintf('News category "%s" was not found', $alias));
                }

                $criteria->setCategory($category->id, (bool) $module->news_filterPreserve, (bool) $module->news_includeSubcategories);
            }
        }
    }

    /**
     * Set the related list criteria.
     *
     * @throws NoNewsException
     */
    private function setRelatedListCriteria(NewsCriteria $criteria, NewsListModule $module): void
    {
        if (null === ($news = $module->currentNews)) {
            throw new NoNewsException();
        }

        /** @var DcaRelationsModel $adapter */
        $adapter = $this->framework->getAdapter(DcaRelationsModel::class);
        $categories = array_unique($adapter->getRelatedValues($news->getTable(), 'categories', $news->id));

        // This news has no news categories assigned
        if (0 === \count($categories)) {
            throw new NoNewsException();
        }

        $categories = array_map('intval', $categories);
        $excluded = $this->db->fetchFirstColumn('SELECT id FROM tl_news_category WHERE excludeInRelated=1');

        // Exclude the categories
        foreach ($excluded as $category) {
            if (false !== ($index = array_search((int) $category, $categories, true))) {
                unset($categories[$index]);
            }
        }

        // Exclude categories by root
        if ($module->news_categoriesRoot > 0) {
            $categories = array_intersect($categories, NewsCategoryModel::getAllSubcategoriesIds($module->news_categoriesRoot));
        }

        // There are no categories left
        if (0 === \count($categories)) {
            throw new NoNewsException();
        }

        $criteria->setDefaultCategories($categories, (bool) $module->news_includeSubcategories, $module->news_relatedCategoriesOrder);
        $criteria->setExcludedNews([$news->id]);
    }
}
