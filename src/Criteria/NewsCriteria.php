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
use Codefog\NewsCategoriesBundle\Exception\NoNewsException;
use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Database;
use Contao\Date;
use Contao\NewsModel;

class NewsCriteria
{
    private array $columns = [];

    private array $values = [];

    private array $options = [];

    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly TokenChecker $tokenChecker,
    ) {
    }

    /**
     * Set the basic criteria.
     *
     * @throws NoNewsException
     */
    public function setBasicCriteria(array $archives, string|null $sorting = null, string|null $featured = null): self
    {
        $archives = $this->parseIds($archives);

        if (0 === \count($archives)) {
            throw new NoNewsException();
        }

        $t = NewsModel::getTable();

        $this->columns[] = "$t.pid IN(".implode(',', array_map('intval', $archives)).')';

        $order = '';

        if ('featured_first' === $featured) {
            $order .= "$t.featured DESC, ";
        }

        match ($sorting) {
            'order_headline_asc' => $order .= "$t.headline",
            'order_headline_desc' => $order .= "$t.headline DESC",
            'order_random' => $order .= 'RAND()',
            'order_date_asc' => $order .= "$t.date",
            default => $order .= "$t.date DESC",
        };

        $this->options['order'] = $order;

        // Never return unpublished elements in the back end, so they don't end up in the RSS feed
        if (!$this->tokenChecker->isPreviewMode()) {
            $time = Date::floorToMinute();
            $this->columns[] = "$t.published=? AND ($t.start=? OR $t.start<=?) AND ($t.stop=? OR $t.stop>?)";
            $this->values = array_merge($this->values, [1, '', $time, '', $time]);
        }

        return $this;
    }

    /**
     * Set the features items.
     *
     * @param bool $enable
     */
    public function setFeatured($enable): self
    {
        $t = NewsModel::getTable();

        if (true === $enable) {
            $this->columns[] = "$t.featured=?";
            $this->values[] = 1;
        } elseif (false === $enable) {
            $this->columns[] = "$t.featured=?";
            $this->values[] = '';
        }

        return $this;
    }

    /**
     * Set the time frame.
     *
     * @param int $begin
     * @param int $end
     */
    public function setTimeFrame($begin, $end): self
    {
        $t = NewsModel::getTable();

        $this->columns[] = "$t.date>=? AND $t.date<=?";
        $this->values[] = $begin;
        $this->values[] = $end;

        return $this;
    }

    /**
     * Set the default categories.
     *
     * @param bool $includeSubcategories
     *
     * @throws NoNewsException
     */
    public function setDefaultCategories(array $defaultCategories, $includeSubcategories = true, string|null $order = null): self
    {
        $defaultCategories = $this->parseIds($defaultCategories);

        if (0 === \count($defaultCategories)) {
            throw new NoNewsException();
        }

        // Include the subcategories
        if ($includeSubcategories) {
            /** @var NewsCategoryModel $newsCategoryModel */
            $newsCategoryModel = $this->framework->getAdapter(NewsCategoryModel::class);
            $defaultCategories = $newsCategoryModel->getAllSubcategoriesIds($defaultCategories);
        }

        /** @var DcaRelationsModel $model */
        $model = $this->framework->getAdapter(DcaRelationsModel::class);

        $newsIds = $model->getReferenceValues('tl_news', 'categories', $defaultCategories);
        $newsIds = $this->parseIds($newsIds);

        if (0 === \count($newsIds)) {
            throw new NoNewsException();
        }

        $t = NewsModel::getTable();

        $this->columns['defaultCategories'] = "$t.id IN(".implode(',', $newsIds).')';

        // Order news items by best match
        if ('best_match' === $order) {
            $mapper = [];

            // Build the mapper
            foreach (array_unique($newsIds) as $newsId) {
                $mapper[$newsId] = \count(array_intersect($defaultCategories, array_unique($model->getRelatedValues($t, 'categories', $newsId))));
            }

            arsort($mapper);

            $this->options['order'] = Database::getInstance()->findInSet("$t.id", array_keys($mapper));
        }

        return $this;
    }

    /**
     * Set the category (intersection filtering).
     *
     * @param int  $category
     * @param bool $preserveDefault
     * @param bool $includeSubcategories
     *
     * @throws NoNewsException
     */
    public function setCategory($category, $preserveDefault = false, $includeSubcategories = false): self
    {
        /** @var DcaRelationsModel $model */
        $model = $this->framework->getAdapter(DcaRelationsModel::class);

        // Include the subcategories
        if ($includeSubcategories) {
            /** @var NewsCategoryModel $newsCategoryModel */
            $newsCategoryModel = $this->framework->getAdapter(NewsCategoryModel::class);
            $category = $newsCategoryModel->getAllSubcategoriesIds($category);
        }

        $newsIds = $model->getReferenceValues('tl_news', 'categories', $category);
        $newsIds = $this->parseIds($newsIds);

        if (0 === \count($newsIds)) {
            throw new NoNewsException();
        }

        // Do not preserve the default categories
        if (!$preserveDefault) {
            unset($this->columns['defaultCategories']);
        }

        $t = NewsModel::getTable();

        $this->columns[] = "$t.id IN(".implode(',', $newsIds).')';

        return $this;
    }

    /**
     * Set the categories (union filtering).
     *
     * @param array $categories
     * @param bool  $preserveDefault
     * @param bool  $includeSubcategories
     *
     * @throws NoNewsException
     */
    public function setCategories($categories, $preserveDefault = false, $includeSubcategories = false): self
    {
        $allNewsIds = [];

        /** @var DcaRelationsModel $model */
        $model = $this->framework->getAdapter(DcaRelationsModel::class);

        foreach ($categories as $category) {
            // Include the subcategories
            if ($includeSubcategories) {
                /** @var NewsCategoryModel $newsCategoryModel */
                $newsCategoryModel = $this->framework->getAdapter(NewsCategoryModel::class);
                $category = $newsCategoryModel->getAllSubcategoriesIds($category);
            }

            $newsIds = $model->getReferenceValues('tl_news', 'categories', $category);
            $newsIds = $this->parseIds($newsIds);

            if (0 === \count($newsIds)) {
                continue;
            }

            $allNewsIds = array_merge($allNewsIds, $newsIds);
        }

        if (0 === \count($allNewsIds)) {
            throw new NoNewsException();
        }

        // Do not preserve the default categories
        if (!$preserveDefault) {
            unset($this->columns['defaultCategories']);
        }

        $t = NewsModel::getTable();

        $this->columns[] = "$t.id IN(".implode(',', $allNewsIds).')';

        return $this;
    }

    /**
     * Set the excluded news IDs.
     */
    public function setExcludedNews(array $newsIds): self
    {
        $newsIds = $this->parseIds($newsIds);

        if ([] === $newsIds) {
            throw new NoNewsException();
        }

        $t = NewsModel::getTable();

        $this->columns[] = "$t.id NOT IN (".implode(',', $newsIds).')';

        return $this;
    }

    /**
     * Set the limit.
     */
    public function setLimit(int $limit): self
    {
        $this->options['limit'] = $limit;

        return $this;
    }

    /**
     * Set the offset.
     */
    public function setOffset(int $offset): self
    {
        $this->options['offset'] = $offset;

        return $this;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Parse the record IDs.
     *
     * @return array<int>
     */
    private function parseIds(array $ids): array
    {
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids);
        $ids = array_unique($ids);

        return array_values($ids);
    }
}
