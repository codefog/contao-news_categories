<?php

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\Model;

use Codefog\NewsCategoriesBundle\MultilingualHelper;
use Contao\Database;
use Contao\Date;
use Contao\FilesModel;
use Contao\Model\Collection;
use Contao\NewsModel;
use Haste\Model\Model;
use Haste\Model\Relations;

/*
 * Use the multilingual model if available
 */
if (MultilingualHelper::isActive()) {
    class ParentModel extends \Terminal42\DcMultilingualBundle\Model\Multilingual
    {
    }
} else {
    class ParentModel extends \Contao\Model
    {
    }
}

class NewsCategoryModel extends ParentModel
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_news_category';

    /**
     * Get the CSS class.
     *
     * @return string
     */
    public function getCssClass()
    {
        $cssClasses = [
            'news_category_'.$this->id,
            'category_'.$this->id,
        ];

        if ($this->cssClass) {
            $cssClasses[] = $this->cssClass;
        }

        return \implode(' ', \array_unique($cssClasses));
    }

    /**
     * Get the image.
     *
     * @return FilesModel|null
     */
    public function getImage()
    {
        return $this->image ? FilesModel::findByPk($this->image) : null;
    }

    /**
     * Get the title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->frontendTitle ?: $this->title;
    }

    /**
     * Find published news categories by news criteria.
     *
     * @param array $archives
     * @param array $ids
     * @param array $aliases
     *
     * @return Collection|null
     */
    public static function findPublishedByArchives(array $archives, array $ids = [], array $aliases = [])
    {
        if (0 === \count($archives) || false === ($relation = Relations::getRelation('tl_news', 'categories'))) {
            return null;
        }

        $t = static::getTableAlias();
        $values = [];

        // Start sub select query for relations
        $subSelect = "SELECT {$relation['related_field']} 
FROM {$relation['table']} 
WHERE {$relation['reference_field']} IN (SELECT id FROM tl_news WHERE pid IN (".\implode(',', \array_map('intval', $archives)).')';

        // Include only the published news items
        if (!BE_USER_LOGGED_IN) {
            $time = Date::floorToMinute();
            $subSelect .= ' AND (start=? OR start<=?) AND (stop=? OR stop>?) AND published=?';
            $values = \array_merge($values, ['', $time, '', $time + 60, 1]);
        }

        // Finish sub select query for relations
        $subSelect .= ')';

        // Columns definition start
        $columns = ["$t.id IN ($subSelect)"];

        // Filter by custom categories
        if (\count($ids) > 0) {
            $columns[] = "$t.id IN (".\implode(',', \array_map('intval', $ids)).')';
        }

        // Filter by custom aliases
        if (\count($aliases) > 0) {
            $columns[] = "$t.alias IN ('".\implode("','", $aliases)."')";
        }

        if (!BE_USER_LOGGED_IN) {
            $columns[] = "$t.published=?";
            $values[] = 1;
        }

        return static::findBy($columns, $values, ['order' => "$t.sorting"]);
    }

    /**
     * Find published category by ID or alias.
     *
     * @param string $idOrAlias
     *
     * @return NewsCategoryModel|null
     */
    public static function findPublishedByIdOrAlias($idOrAlias)
    {
        $values = [];
        $columns = [];
        $t = static::getTableAlias();

        // Determine the alias condition
        if (is_numeric($idOrAlias)) {
            $columns[] = "$t.id=?";
            $values[] = (int) $idOrAlias;
        } else {
            if (MultilingualHelper::isActive()) {
                $columns[] = '(t1.alias=? OR t2.alias=?)';
                $values[] = $idOrAlias;
                $values[] = $idOrAlias;
            } else {
                $columns[] = "$t.alias=?";
                $values[] = $idOrAlias;
            }
        }

        if (!BE_USER_LOGGED_IN) {
            $columns[] = "$t.published=?";
            $values[] = 1;
        }

        return static::findOneBy($columns, $values);
    }

    /**
     * Find published news categories.
     *
     * @return Collection|null
     */
    public static function findPublished()
    {
        $t = static::getTableAlias();
        $options = ['order' => "$t.sorting"];

        if (BE_USER_LOGGED_IN) {
            return static::findAll($options);
        }

        return static::findBy('published', 1, $options);
    }

    /**
     * Find published news categories by parent ID and IDs.
     *
     * @param array    $ids
     * @param int|null $pid
     *
     * @return Collection|null
     */
    public static function findPublishedByIds(array $ids, $pid = null)
    {
        if (0 === \count($ids)) {
            return null;
        }

        $t = static::getTableAlias();
        $columns = ["$t.id IN (".\implode(',', \array_map('intval', $ids)).')'];
        $values = [];

        // Filter by pid
        if (null !== $pid) {
            $columns[] = "$t.pid=?";
            $values[] = $pid;
        }

        if (!BE_USER_LOGGED_IN) {
            $columns[] = "$t.published=?";
            $values[] = 1;
        }

        return static::findBy($columns, $values, ['order' => "$t.sorting"]);
    }

    /**
     * Find the published categories by news.
     *
     * @param int|array $newsId
     *
     * @return Collection|null
     */
    public static function findPublishedByNews($newsId)
    {
        if (0 === \count($ids = Model::getRelatedValues('tl_news', 'categories', $newsId))) {
            return null;
        }

        $t = static::getTableAlias();
        $columns = ["$t.id IN (".\implode(',', \array_map('intval', \array_unique($ids))).')'];
        $values = [];

        if (!BE_USER_LOGGED_IN) {
            $columns[] = "$t.published=?";
            $values[] = 1;
        }

        return static::findBy($columns, $values, ['order' => "$t.sorting"]);
    }

    /**
     * Count the published news by archives.
     *
     * @param array    $archives
     * @param int|null $category
     * @param bool     $includeSubcategories
     * @param array    $cumulativeCategories
     *
     * @return int
     */
    public static function getUsage(array $archives = [], $category = null, $includeSubcategories = false, array $cumulativeCategories = [])
    {
        $t = NewsModel::getTable();

        // Include the subcategories
        if (null !== $category && $includeSubcategories) {
            $category = static::getAllSubcategoriesIds($category);
        }

        $ids = Model::getReferenceValues($t, 'categories', $category);

        // Also filter by cumulative categories
        if (count($cumulativeCategories) > 0) {
            $cumulativeIds = null;

            foreach ($cumulativeCategories as $cumulativeCategory) {
                $tmp = Model::getReferenceValues($t, 'categories', $cumulativeCategory);

                if ($cumulativeIds === null) {
                    $cumulativeIds = $tmp;
                } else {
                    $cumulativeIds = array_intersect($cumulativeIds, $tmp);
                }
            }

            $ids = array_intersect($ids, $cumulativeIds);
        }

        if (0 === \count($ids)) {
            return 0;
        }

        $columns = ["$t.id IN (".\implode(',', \array_unique($ids)).')'];
        $values = [];

        // Filter by archives
        if (\count($archives)) {
            $columns[] = "$t.pid IN (".\implode(',', \array_map('intval', $archives)).')';
        }

        if (!BE_USER_LOGGED_IN) {
            $time = Date::floorToMinute();
            $columns[] = "($t.start=? OR $t.start<=?) AND ($t.stop=? OR $t.stop>?) AND $t.published=?";
            $values = \array_merge($values, ['', $time, '', $time + 60, 1]);
        }

        return NewsModel::countBy($columns, $values);
    }

    /**
     * Get all subcategory IDs.
     *
     * @param array|int $category
     *
     * @return array
     */
    public static function getAllSubcategoriesIds($category)
    {
        $ids = Database::getInstance()->getChildRecords($category, static::$strTable, false, (array) $category, (!BE_USER_LOGGED_IN ? 'published=1' : ''));
        $ids = \array_map('intval', $ids);

        return $ids;
    }

    /**
     * {@inheritdoc}
     */
    public static function findMultipleByIds($arrIds, array $arrOptions = [])
    {
        if (!MultilingualHelper::isActive()) {
            return parent::findMultipleByIds($arrIds, $arrOptions);
        }

        $t = static::getTableAlias();

        if (!isset($arrOptions['order'])) {
            $arrOptions['order'] = Database::getInstance()->findInSet("$t.id", $arrIds);
        }

        return static::findBy(["$t.id IN (".\implode(',', \array_map('intval', $arrIds)).')'], null);
    }

    /**
     * Get the table alias.
     *
     * @return string
     */
    public static function getTableAlias()
    {
        if (MultilingualHelper::isActive()) {
            return 't1';
        }

        return static::$strTable;
    }
}
