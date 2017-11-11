<?php

namespace Codefog\NewsCategoriesBundle;

use Codefog\NewsCategoriesBundle\Exception\NoNewsException;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Date;
use Contao\NewsModel;
use Haste\Model\Model;
use Haste\Model\Relations;

class Criteria
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
     * Criteria constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Set the basic criteria
     *
     * @param array     $archives
     * @param bool|null $featured
     *
     * @throws NoNewsException
     */
    public function setBasicCriteria(array $archives, $featured)
    {
        $archives = $this->parseIds($archives);

        if (count($archives) === 0) {
            throw new NoNewsException();
        }

        $t = $this->getNewsModelAdapter()->getTable();

        $this->columns[] = "$t.pid IN(" . implode(',', array_map('intval', $archives)) . ")";
        $this->options['order'] = "$t.date DESC";

        // Handle the featured settings
        if ($featured === true) {
            $this->columns[] = "$t.featured=?";
            $this->values[] = 1;
        } elseif ($featured === false) {
            $this->columns[] = "$t.featured=?";
            $this->values[] = '';
        }

        // Never return unpublished elements in the back end, so they don't end up in the RSS feed
        if (!BE_USER_LOGGED_IN || TL_MODE === 'BE') {
            /** @var Date $dateAdapter */
            $dateAdapter = $this->framework->getAdapter(Date::class);

            $time = $dateAdapter->floorToMinute();
            $this->columns[] = "($t.start=? OR $t.start<=?') AND ($t.stop=? OR $t.stop>?) AND $t.published=?";
            $this->values = array_merge($this->values, ['', $time, '', ($time + 60), 1]);
        }
    }

    /**
     * Set the default categories
     *
     * @param array $defaultCategories
     *
     * @throws NoNewsException
     */
    public function setDefaultCategories(array $defaultCategories)
    {
        $defaultCategories = $this->parseIds($defaultCategories);

        if (count($defaultCategories) === 0) {
            throw new NoNewsException();
        }

        /** @var Model $model */
        $model = $this->framework->getAdapter(Model::class);
        $newsIds = $model->getReferenceValues('tl_news', 'categories', $defaultCategories);

        if (count($newsIds) === 0) {
            throw new NoNewsException();
        }

        $t = $this->getNewsModelAdapter()->getTable();

        $this->columns['defaultCategories'] = "$t.id IN(" . implode(',', $newsIds) . ")";
    }

    /**
     * Set the category
     *
     * @param int  $category
     * @param bool $preserveDefault
     *
     * @return NoNewsException
     */
    public function setCategory($category, $preserveDefault = false)
    {
        /** @var Model $model */
        $model = $this->framework->getAdapter(Model::class);
        $newsIds = $model->getReferenceValues('tl_news', 'categories', (int) $category);

        if (count($newsIds) === 0) {
            throw new NoNewsException();
        }

        // Do not preserve the default categories
        if (!$preserveDefault) {
            unset($this->columns['defaultCategories']);
        }

        $t = $this->getNewsModelAdapter()->getTable();

        $this->columns[] = "$t.id IN(" . implode(',', $newsIds) . ")";
    }

    /**
     * Set the excluded news IDs
     *
     * @param array $newsIds
     */
    public function setExcludedNews(array $newsIds)
    {
        $newsIds = $this->parseIds($newsIds);

        if (count($newsIds) === 0) {
            throw new NoNewsException();
        }

        $t = $this->getNewsModelAdapter()->getTable();

        $this->columns[] = "$t.id NOT IN (" . implode(',', $newsIds) . ")";
    }

    /**
     * Set the limit
     *
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->options['limit'] = $limit;
    }

    /**
     * Set the offset
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
     * Get the news model adapter
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
     * Parse the record IDs
     *
     * @param array $ids
     *
     * @return array
     */
    private function parseIds(array $ids)
    {
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids);
        $ids = array_unique($ids);

        return array_values($ids);
    }
}
