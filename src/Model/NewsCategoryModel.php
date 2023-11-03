<?php

declare(strict_types=1);

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\Model;

use Codefog\HasteBundle\DcaRelationsManager;
use Codefog\HasteBundle\Model\DcaRelationsModel;
use Contao\Database;
use Contao\Date;
use Contao\FilesModel;
use Contao\Model;
use Contao\Model\Collection;
use Contao\NewsModel;
use Contao\System;
use Terminal42\DcMultilingualBundle\Model\Multilingual;

/*
 * Use the multilingual model if available
 */
if (class_exists(Multilingual::class)) {
    class NewsCategoryParentModel extends Multilingual
    {
    }
} else {
    class NewsCategoryParentModel extends Model
    {
        public function getAlias(): string
        {
            return $this->alias;
        }
    }
}

/**
 * @method string getAlias(string $language)
 */
class NewsCategoryModel extends NewsCategoryParentModel
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_news_category';

    public function __get($strKey)
    {
        // Fix the compatibility with DC_Multilingual v4 (#184)
        if ('id' === $strKey && self::isMultilingual() && $this->lid) {
            return $this->lid;
        }

        return parent::__get($strKey);
    }

    /**
     * Get the CSS class.
     */
    public function getCssClass(): string
    {
        $cssClasses = [
            'news_category_'.$this->id,
            'category_'.$this->id,
        ];

        if ($this->cssClass) {
            $cssClasses[] = $this->cssClass;
        }

        return implode(' ', array_unique($cssClasses));
    }

    /**
     * Get the image.
     */
    public function getImage(): FilesModel|null
    {
        return $this->image ? FilesModel::findByPk($this->image) : null;
    }

    /**
     * Get the title.
     */
    public function getTitle(): string
    {
        return $this->frontendTitle ?: $this->title;
    }

    /**
     * Find published news categories by news criteria.
     *
     * @return Collection<static>|null
     */
    public static function findPublishedByArchives(array $archives, array $ids = [], array $aliases = [], array $excludedIds = [], array $arrOptions = []): Collection|null
    {
        if (0 === \count($archives)) {
            return null;
        }

        $dcaRelationsManager = System::getContainer()->get(DcaRelationsManager::class);

        if (null === ($relation = $dcaRelationsManager->getRelation('tl_news', 'categories'))) {
            return null;
        }

        $t = static::getTable();
        $values = [];

        // Start sub select query for relations
        $subSelect = "SELECT {$relation['related_field']}
FROM {$relation['table']}
WHERE {$relation['reference_field']} IN (SELECT id FROM tl_news WHERE pid IN (".implode(',', array_map('intval', $archives)).')';

        // Include only the published news items
        if (!self::isPreviewMode($arrOptions)) {
            $time = Date::floorToMinute();
            $subSelect .= ' AND (start=? OR start<=?) AND (stop=? OR stop>?) AND published=?';
            $values = array_merge($values, ['', $time, '', $time + 60, 1]);
        }

        // Finish sub select query for relations
        $subSelect .= ')';

        // Columns definition start
        $columns = ["$t.id IN ($subSelect)"];

        // Filter by custom categories
        if (\count($ids) > 0) {
            $columns[] = "$t.id IN (".implode(',', array_map('intval', $ids)).')';
        }

        // Filter by excluded IDs
        if (\count($excludedIds) > 0) {
            $columns[] = "$t.id NOT IN (".implode(',', array_map('intval', $excludedIds)).')';
        }

        // Filter by custom aliases
        if (\count($aliases) > 0) {
            if (self::isMultilingual()) {
                $columns[] = "($t.alias IN ('".implode("','", $aliases)."') OR translation.alias IN ('".implode("','", $aliases)."'))";
            } else {
                $columns[] = "$t.alias IN ('".implode("','", $aliases)."')";
            }
        }

        if (!self::isPreviewMode($arrOptions)) {
            $columns[] = "$t.published=?";
            $values[] = 1;
        }

        return static::findBy($columns, $values, array_merge(['order' => "$t.sorting"], $arrOptions));
    }

    /**
     * Find published category by ID or alias.
     */
    public static function findPublishedByIdOrAlias(string $idOrAlias, array $arrOptions = []): self|null
    {
        $values = [];
        $columns = [];
        $t = static::getTable();

        // Determine the alias condition
        if (is_numeric($idOrAlias)) {
            $columns[] = "$t.id=?";
            $values[] = (int) $idOrAlias;
        } else {
            if (self::isMultilingual()) {
                $columns[] = "($t.alias=? OR translation.alias=?)";
                $values[] = $idOrAlias;
                $values[] = $idOrAlias;
            } else {
                $columns[] = "$t.alias=?";
                $values[] = $idOrAlias;
            }
        }

        if (!self::isPreviewMode($arrOptions)) {
            $columns[] = "$t.published=?";
            $values[] = 1;
        }

        return static::findOneBy($columns, $values, $arrOptions);
    }

    /**
     * Find published news categories.
     *
     * @return Collection<static>|null
     */
    public static function findPublished(array $arrOptions = []): Collection|null
    {
        $t = static::getTable();
        $arrOptions = array_merge(['order' => "$t.sorting"], $arrOptions);

        if (self::isPreviewMode($arrOptions)) {
            return static::findAll($arrOptions);
        }

        return static::findBy('published', 1, $arrOptions);
    }

    /**
     * Find published news categories by parent ID and IDs.
     *
     * @return Collection<static>|null
     */
    public static function findPublishedByIds(array $ids, int|null $pid = null, array $arrOptions = []): Collection|null
    {
        if (0 === \count($ids)) {
            return null;
        }

        $t = static::getTable();
        $columns = ["$t.id IN (".implode(',', array_map('intval', $ids)).')'];
        $values = [];

        // Filter by pid
        if (null !== $pid) {
            $columns[] = "$t.pid=?";
            $values[] = $pid;
        }

        if (!self::isPreviewMode($arrOptions)) {
            $columns[] = "$t.published=?";
            $values[] = 1;
        }

        return static::findBy($columns, $values, array_merge(['order' => "$t.sorting"], $arrOptions));
    }

    /**
     * Find published news categories by parent ID.
     *
     * @return Collection<static>|null
     */
    public static function findPublishedByPid(int $pid, array $arrOptions = []): Collection|null
    {
        $t = static::getTable();
        $columns = ["$t.pid=?"];
        $values = [$pid];

        if (!self::isPreviewMode($arrOptions)) {
            $columns[] = "$t.published=?";
            $values[] = 1;
        }

        return static::findBy($columns, $values, array_merge(['order' => "$t.sorting"], $arrOptions));
    }

    /**
     * Find the published categories by news.
     *
     * @return Collection<static>|array|null
     */
    public static function findPublishedByNews(array|int $newsId, array $arrOptions = []): Collection|array|null
    {
        if (0 === \count($ids = DcaRelationsModel::getRelatedValues('tl_news', 'categories', $newsId))) {
            return null;
        }

        $t = static::getTable();
        $columns = ["$t.id IN (".implode(',', array_map('intval', array_unique($ids))).')'];
        $values = [];

        if (!self::isPreviewMode($arrOptions)) {
            $columns[] = "$t.published=?";
            $values[] = 1;
        }

        return static::findBy($columns, $values, array_merge(['order' => "$t.sorting"], $arrOptions));
    }

    /**
     * Count the published news by archives.
     *
     * @param bool $includeSubcategories
     * @param bool $unionFiltering
     *
     * @return int
     */
    public static function getUsage(array $archives = [], int|null $category = null, $includeSubcategories = false, array $cumulativeCategories = [], $unionFiltering = false, array $arrOptions = [])
    {
        $t = NewsModel::getTable();

        // Include the subcategories
        if (null !== $category && $includeSubcategories) {
            $category = static::getAllSubcategoriesIds($category);
        }

        $ids = DcaRelationsModel::getReferenceValues($t, 'categories', $category);
        $ids = array_map('intval', $ids);

        // Also filter by cumulative categories
        if (\count($cumulativeCategories) > 0) {
            $cumulativeIds = null;

            foreach ($cumulativeCategories as $cumulativeCategory) {
                // Include the subcategories
                if ($includeSubcategories) {
                    $cumulativeCategory = static::getAllSubcategoriesIds($cumulativeCategory);
                }

                $newsIds = DcaRelationsModel::getReferenceValues($t, 'categories', $cumulativeCategory);
                $newsIds = array_map('intval', $newsIds);

                if (null === $cumulativeIds) {
                    $cumulativeIds = $newsIds;
                } else {
                    $cumulativeIds = $unionFiltering ? array_merge($cumulativeIds, $newsIds) : array_intersect($cumulativeIds, $newsIds);
                }
            }

            $ids = $unionFiltering ? array_merge($ids, $cumulativeIds) : array_intersect($ids, $cumulativeIds);
        }

        if (0 === \count($ids)) {
            return 0;
        }

        $columns = ["$t.id IN (".implode(',', array_unique($ids)).')'];
        $values = [];

        // Filter by archives
        if (\count($archives)) {
            $columns[] = "$t.pid IN (".implode(',', array_map('intval', $archives)).')';
        }

        if (!self::isPreviewMode($arrOptions)) {
            $time = Date::floorToMinute();
            $columns[] = "$t.published=? AND ($t.start=? OR $t.start<=?) AND ($t.stop=? OR $t.stop>?)";
            $values = array_merge($values, [1, '', $time, '', $time]);
        }

        return NewsModel::countBy($columns, $values, $arrOptions);
    }

    /**
     * Get all subcategory IDs.
     */
    public static function getAllSubcategoriesIds(array|int|string $category): array
    {
        $ids = Database::getInstance()->getChildRecords($category, static::$strTable, false, (array) $category, !self::isPreviewMode([]) ? 'published=1' : '');
        $ids = array_map('intval', $ids);

        return $ids;
    }

    /**
     * @return Collection<static>|null
     */
    public static function findMultipleByIds($arrIds, array $arrOptions = []): Collection|null
    {
        if (!self::isMultilingual()) {
            return parent::findMultipleByIds($arrIds, $arrOptions);
        }

        $t = static::getTable();

        if (!isset($arrOptions['order'])) {
            $arrOptions['order'] = Database::getInstance()->findInSet("$t.id", $arrIds);
        }

        return static::findBy(["$t.id IN (".implode(',', array_map('intval', $arrIds)).')'], null, $arrOptions);
    }

    private static function isMultilingual(): bool
    {
        return is_a(self::class, Multilingual::class, true);
    }
}
