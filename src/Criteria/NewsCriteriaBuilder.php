<?php

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\Criteria;

use Codefog\HasteBundle\Model\DcaRelationsModel;
use Codefog\NewsCategoriesBundle\Exception\CategoryFilteringNotAppliedException;
use Codefog\NewsCategoriesBundle\Exception\CategoryNotFoundException;
use Codefog\NewsCategoriesBundle\Exception\NoNewsException;
use Codefog\NewsCategoriesBundle\FrontendModule\CumulativeFilterModule;
use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Codefog\NewsCategoriesBundle\NewsCategoriesManager;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\Input;
use Contao\Module;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;

class NewsCriteriaBuilder implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    /**
     * @var Connection
     */
    private $db;

    /**
     * @var NewsCategoriesManager
     */
    private $manager;

    /**
     * NewsCriteriaBuilder constructor.
     *
     * @param Connection            $db
     * @param NewsCategoriesManager $manager
     */
    public function __construct(Connection $db, NewsCategoriesManager $manager)
    {
        $this->db = $db;
        $this->manager = $manager;
    }

    /**
     * Get the criteria for archive module.
     *
     * @param array  $archives
     * @param int    $begin
     * @param int    $end
     * @param Module $module
     *
     * @return NewsCriteria|null
     */
    public function getCriteriaForArchiveModule(array $archives, $begin, $end, Module $module)
    {
        $criteria = new NewsCriteria($this->framework);

        try {
            $criteria->setBasicCriteria($archives, $module->news_order);

            // Set the time frame
            $criteria->setTimeFrame($begin, $end);

            // Set the regular list criteria
            $this->setRegularListCriteria($criteria, $module);
        } catch (NoNewsException $e) {
            return null;
        } catch (CategoryFilteringNotAppliedException $e) {
            // noop
        }

        return $criteria;
    }

    /**
     * Get the criteria for list module.
     *
     * @param array     $archives
     * @param bool|null $featured
     * @param Module    $module
     *
     * @return NewsCriteria|null
     */
    public function getCriteriaForListModule(array $archives, $featured, Module $module, bool $throwOnFilteringNotApplied = false)
    {
        $criteria = new NewsCriteria($this->framework);

        try {
            $criteria->setBasicCriteria($archives, $module->news_order, $module->news_featured);

            // Set the featured filter
            if (null !== $featured) {
                $criteria->setFeatured($featured);
            }

            // Set the criteria for related categories
            if ($module->news_relatedCategories) {
                $this->setRelatedListCriteria($criteria, $module);
            } else {
                // Set the regular list criteria
                $this->setRegularListCriteria($criteria, $module, $throwOnFilteringNotApplied);
            }
        } catch (NoNewsException $e) {
            return null;
        } catch (CategoryFilteringNotAppliedException $e) {
            if ($throwOnFilteringNotApplied) {
                throw $e;
            }
        }

        return $criteria;
    }

    /**
     * Get the criteria for menu module.
     *
     * @param array  $archives
     * @param Module $module
     *
     * @return NewsCriteria|null
     */
    public function getCriteriaForMenuModule(array $archives, Module $module)
    {
        $criteria = new NewsCriteria($this->framework);

        try {
            $criteria->setBasicCriteria($archives, $module->news_order);

            // Set the regular list criteria
            $this->setRegularListCriteria($criteria, $module);
        } catch (NoNewsException $e) {
            return null;
        } catch (CategoryFilteringNotAppliedException $e) {
            // noop
        }

        return $criteria;
    }

    /**
     * Set the regular list criteria.
     *
     * @param NewsCriteria $criteria
     * @param Module       $module
     *
     * @throws CategoryNotFoundException
     * @throws NoNewsException
     */
    private function setRegularListCriteria(NewsCriteria $criteria, Module $module)
    {
        $filteringApplied = false;

        // Filter by default categories
        if (\count($default = StringUtil::deserialize($module->news_filterDefault, true)) > 0) {
            $criteria->setDefaultCategories($default);
            $filteringApplied = true;
        }

        // Filter by multiple active categories
        if ($module->news_filterCategoriesCumulative) {
            /** @var Input $input */
            $input = $this->framework->getAdapter(Input::class);
            $param = $this->manager->getParameterName();

            if ($aliases = $input->get($param)) {
                $aliases = StringUtil::trimsplit(CumulativeFilterModule::getCategorySeparator(), $aliases);
                $aliases = array_unique(array_filter($aliases));

                if (count($aliases) > 0) {
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

                    if (count($categories) > 0) {
                        // Union filtering
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

                $filteringApplied = true;
            }
        }
        // Filter by active category
        elseif ($module->news_filterCategories) {
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

                $filteringApplied = true;
            }
        }

        if (!$filteringApplied) {
            throw new CategoryFilteringNotAppliedException();
        }
    }

    /**
     * Set the related list criteria.
     *
     * @param NewsCriteria $criteria
     * @param Module       $module
     *
     * @throws NoNewsException
     */
    private function setRelatedListCriteria(NewsCriteria $criteria, Module $module)
    {
        if (null === ($news = $module->currentNews)) {
            throw new NoNewsException();
        }

        /** @var DcaRelationsModel $adapter */
        $adapter = $this->framework->getAdapter(DcaRelationsModel::class);
        $categories = \array_unique($adapter->getRelatedValues($news->getTable(), 'categories', $news->id));

        // This news has no news categories assigned
        if (0 === \count($categories)) {
            throw new NoNewsException();
        }

        $categories = \array_map('intval', $categories);
        $excluded = $this->db->fetchFirstColumn('SELECT id FROM tl_news_category WHERE excludeInRelated=1');

        // Exclude the categories
        foreach ($excluded as $category) {
            if (false !== ($index = \array_search((int) $category, $categories, true))) {
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
