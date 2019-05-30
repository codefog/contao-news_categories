<?php

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\Criteria;

use Codefog\NewsCategoriesBundle\Exception\NoNewsException;
use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Database;
use Contao\Date;
use Contao\NewsModel;
use Haste\Model\Model;

class NewsCriteria
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var array
     */
    private $columns = [];

    /**
     * @var array
     */
    private $values = [];

    /**
     * @var array
     */
    private $options = [];

    /**
     * NewsCriteria constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Set the basic criteria.
     *
     * @param array  $archives
     * @param string $sorting
     *
     * @throws NoNewsException
     */
    public function setBasicCriteria(array $archives, $sorting = null)
    {
        $archives = $this->parseIds($archives);

        if (0 === \count($archives)) {
            throw new NoNewsException();
        }

        $t = $this->getNewsModelAdapter()->getTable();

        $this->columns[] = "$t.pid IN(".\implode(',', \array_map('intval', $archives)).')';

        // Set the sorting
        switch ($sorting) {
            case 'order_headline_asc':
                $this->options['order'] = "$t.headline";
                break;
            case 'order_headline_desc':
                $this->options['order'] = "$t.headline DESC";
                break;
            case 'order_random':
                $this->options['order'] = 'RAND()';
                break;
            case 'order_date_asc':
                $this->options['order'] = "$t.date";
                break;
            case 'order_featured_desc':
                $this->options['order'] = "$t.featured DESC, $t.date DESC";
                break;
            default:
                $this->options['order'] = "$t.date DESC";
                break;
        }

        // Never return unpublished elements in the back end, so they don't end up in the RSS feed
        if (!BE_USER_LOGGED_IN || TL_MODE === 'BE') {
            /** @var Date $dateAdapter */
            $dateAdapter = $this->framework->getAdapter(Date::class);

            $time = $dateAdapter->floorToMinute();
            $this->columns[] = "($t.start=? OR $t.start<=?) AND ($t.stop=? OR $t.stop>?) AND $t.published=?";
            $this->values = \array_merge($this->values, ['', $time, '', ($time + 60), 1]);
        }
    }

    /**
     * Set the features items.
     *
     * @param bool $enable
     */
    public function setFeatured($enable)
    {
        $t = $this->getNewsModelAdapter()->getTable();

        if (true === $enable) {
            $this->columns[] = "$t.featured=?";
            $this->values[] = 1;
        } elseif (false === $enable) {
            $this->columns[] = "$t.featured=?";
            $this->values[] = '';
        }
    }

    /**
     * Set the time frame.
     *
     * @param int $begin
     * @param int $end
     */
    public function setTimeFrame($begin, $end)
    {
        $t = $this->getNewsModelAdapter()->getTable();

        $this->columns[] = "$t.date>=? AND $t.date<=?";
        $this->values[] = $begin;
        $this->values[] = $end;
    }

    /**
     * Set the default categories.
     *
     * @param array       $defaultCategories
     * @param bool        $includeSubcategories
     * @param string|null $order
     *
     * @throws NoNewsException
     */
    public function setDefaultCategories(array $defaultCategories, $includeSubcategories = true, $order = null)
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

        /** @var Model $model */
        $model = $this->framework->getAdapter(Model::class);

        $newsIds = $model->getReferenceValues('tl_news', 'categories', $defaultCategories);
        $newsIds = $this->parseIds($newsIds);

        if (0 === \count($newsIds)) {
            throw new NoNewsException();
        }

        $t = $this->getNewsModelAdapter()->getTable();

        $this->columns['defaultCategories'] = "$t.id IN(".\implode(',', $newsIds).')';

        // Order news items by best match
        if ($order === 'best_match') {
            $mapper = [];

            // Build the mapper
            foreach (array_unique($newsIds) as $newsId) {
                $mapper[$newsId] = count(array_intersect($defaultCategories, array_unique($model->getRelatedValues($t, 'categories', $newsId))));
            }

            arsort($mapper);

            $this->options['order'] = Database::getInstance()->findInSet("$t.id", array_keys($mapper));
        }
    }

    /**
     * Set the category.
     *
     * @param int  $category
     * @param bool $preserveDefault
     * @param bool $includeSubcategories
     *
     * @return NoNewsException
     */
    public function setCategory($category, $preserveDefault = false, $includeSubcategories = false)
    {
        /** @var Model $model */
        $model = $this->framework->getAdapter(Model::class);

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

        $t = $this->getNewsModelAdapter()->getTable();

        $this->columns[] = "$t.id IN(".\implode(',', $newsIds).')';
    }

    /**
     * Set the excluded news IDs.
     *
     * @param array $newsIds
     */
    public function setExcludedNews(array $newsIds)
    {
        $newsIds = $this->parseIds($newsIds);

        if (0 === \count($newsIds)) {
            throw new NoNewsException();
        }

        $t = $this->getNewsModelAdapter()->getTable();

        $this->columns[] = "$t.id NOT IN (".\implode(',', $newsIds).')';
    }

    /**
     * Set the limit.
     *
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->options['limit'] = $limit;
    }

    /**
     * Set the offset.
     *
     * @param int $offset
     */
    public function setOffset($offset)
    {
        $this->options['offset'] = $offset;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Get the news model adapter.
     *
     * @return NewsModel
     */
    public function getNewsModelAdapter()
    {
        /** @var NewsModel $adapter */
        $adapter = $this->framework->getAdapter(NewsModel::class);

        return $adapter;
    }

    /**
     * Parse the record IDs.
     *
     * @param array $ids
     *
     * @return array
     */
    private function parseIds(array $ids)
    {
        $ids = \array_map('intval', $ids);
        $ids = \array_filter($ids);
        $ids = \array_unique($ids);

        return \array_values($ids);
    }
}
