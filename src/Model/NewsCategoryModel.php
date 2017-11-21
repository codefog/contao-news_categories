<?php

namespace Codefog\NewsCategoriesBundle\Model;

use Contao\Database;
use Contao\Date;
use Contao\Model\Collection;
use Contao\NewsModel;
use Contao\System;
use Haste\Model\Model;

/**
 * Use the multilingual model if available
 */
if (array_key_exists('Terminal42DcMultilingualBundle', System::getContainer()->getParameter('kernel.bundles'))) {
    class ParentModel extends \Terminal42\DcMultilingualBundle\Model\Multilingual {}
} else {
    class ParentModel extends \Contao\Model {}
}

class NewsCategoryModel extends ParentModel
{
    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_news_category';

    /**
     * Find published news categories by their archives
     *
     * @param array $archives
     * @param array $ids
     *
     * @return Collection|null
     */
    public static function findPublishedByParent(array $archives, array $ids = [])
    {
        if (count($archives) === 0 || count($categoryIds = Model::getRelatedValues('tl_news', 'categories')) === 0) {
            return null;
        }

        $t = static::$strTable;
        $time = time();
        $columns = ["$t.id IN (SELECT category_id FROM tl_news_categories WHERE news_id IN (SELECT id FROM tl_news WHERE pid IN (" . implode(',', array_map('intval', $archives)) . ")" . (!BE_USER_LOGGED_IN ? " AND (tl_news.start='' OR tl_news.start<$time) AND (tl_news.stop='' OR tl_news.stop>$time) AND tl_news.published=1" : "") . "))"];
        $values = [];

        // Filter by custom categories
        if (count($ids) > 0) {
            $columns[] = "$t.id IN (" . implode(',', array_map('intval', $ids)) . ")";
        }

        if (!BE_USER_LOGGED_IN) {
            $columns[] = "$t.published=?";
            $values[] = 1;
        }

        return static::findBy($columns, $values, ['order' => "$t.sorting"]);
    }

    /**
     * Find published category by ID or alias
     *
     * @param string $idOrAlias
     *
     * @return NewsCategoryModel|null
     */
    public static function findPublishedByIdOrAlias($idOrAlias)
    {
        $t = static::$strTable;
        $columns = ["($t.id=? OR $t.alias=?)"];
        $values = [$idOrAlias, $idOrAlias];

        if (!BE_USER_LOGGED_IN) {
            $columns[] = "$t.published=?";
            $values[] = 1;
        }

        return static::findOneBy($columns, $values);
    }

    /**
     * Find published categories by IDs
     *
     * @param array $ids
     *
     * @return Collection|null
     */
    public static function findPublishedByIds(array $ids)
    {
        if (count($ids) === 0) {
            return null;
        }

        $t = static::$strTable;
        $columns = ["$t.id IN (" . implode(',', array_map('intval', $ids)) . ")"];
        $values = [];

        if (!BE_USER_LOGGED_IN) {
            $columns[] = "$t.published=?";
            $values[] = 1;
        }

        return static::findBy($columns, $values, ['order' => "$t.sorting"]);
    }

    /**
     * Find published news categories by parent ID and IDs
     *
     * @param integer $pid
     * @param array   $ids
     *
     * @return Collection|null
     */
    public static function findPublishedByPidAndIds($pid, array $ids)
    {
        if (count($ids) === 0) {
            return null;
        }

        $categories = Database::getInstance()
            ->prepare("SELECT c1.*, (SELECT COUNT(*) FROM tl_news_category c2 WHERE c2.pid=c1.id AND c2.id IN (" . implode(',', array_map('intval', $ids)) . ")" . (!BE_USER_LOGGED_IN ? " AND c2.published=1" : "") . ") AS subcategories FROM tl_news_category c1 WHERE c1.pid=? AND c1.id IN (" . implode(',', array_map('intval', $ids)) . ")" . (!BE_USER_LOGGED_IN ? " AND c1.published=1" : "") . " ORDER BY c1.sorting")
            ->execute($pid);

        if ($categories->numRows < 1) {
            return null;
        }

        return Collection::createFromDbResult($categories, static::$strTable);
    }

    /**
     * Find the published categories by news
     *
     * @param int $newsId
     *
     * @return Collection|null
     */
    public static function findPublishedByNews($newsId)
    {
        $ids = Model::getRelatedValues('tl_news', 'categories', $newsId);
        $ids = array_unique($ids);

        if (count($ids) === 0) {
            return null;
        }

        return static::findPublishedByIds($ids);
    }

    /**
     * Count the published news by archives
     *
     * @param array    $archives
     * @param int|null $category
     *
     * @return int
     */
    public static function countPublishedNewsByArchives(array $archives, $category = null)
    {
        if (count($archives) === 0) {
            return 0;
        }

        $t = NewsModel::getTable();
        $ids = Model::getReferenceValues($t, 'categories', $category);

        if (count($ids) === 0) {
            return 0;
        }

        $columns[] = "$t.pid IN (" . implode(',', array_map('intval', $archives)) . ")";
        $columns[] = "$t.id IN (" . implode(',', array_unique($ids)) . ")";

        $values = [];

        if (!BE_USER_LOGGED_IN) {
            $time = Date::floorToMinute();
            $columns[] = "($t.start=? OR $t.start<=?) AND ($t.stop=? OR $t.stop>?) AND $t.published=?";
            $values = array_merge($values, ['', $time, '', $time + 60, 1]);
        }

        return NewsModel::countBy($columns, $values);
    }
}
