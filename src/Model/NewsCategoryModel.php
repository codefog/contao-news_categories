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
use Codefog\NewsCategoriesBundle\MultilingualHelper;
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
if (MultilingualHelper::isActive()) {
    class ParentModel extends Multilingual
    {
    }
} else {
    class ParentModel extends Model
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
     * {@inheritDoc}
     */
    public function __get($name)
    {
        // Fix the compatibility with DC_Multilingual v4 (#184)
        if ('id' === $name && MultilingualHelper::isActive() && $this->lid) {
            return $this->lid;
        }

        return parent::__get($name);
    }

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
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->frontendTitle ?: $this->title;
    }

    /**
     * Find published news categories by news criteria.
     */
    public static function findPublishedByArchives(array $archives, array $ids = [], array $aliases = [], array $excludedIds = []): Collection|null
    {
        if (0 === \count($archives)) {
            return null;
        }

        /** @var DcaRelationsManager $dcaRelationsManager */
        $dcaRelationsManager = System::getContainer()->get(DcaRelationsManager::class);

        if (null === ($relation = $dcaRelationsManager->getRelation('tl_news', 'categories'))) {
            return null;
        }

        $t = static::getTableAlias();
        $values = [];

        // Start sub select query for relations
        $subSelect = "SELECT {$relation['related_field']}
FROM {$relation['table']}
WHERE {$relation['reference_field']} IN (SELECT id FROM tl_news WHERE pid IN (".implode(',', array_map('intval', $archives)).')';

        // Include only the published news items
        if (!System::getContainer()->get('contao.security.token_checker')->isPreviewMode()) {
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
            if (MultilingualHelper::isActive()) {
                $columns[] = "($t.alias IN ('".implode("','", $aliases)."') OR translation.alias IN ('".implode("','", $aliases)."'))";
            } else {
                $columns[] = "$t.alias IN ('".implode("','", $aliases)."')";
            }
        }

        if (!System::getContainer()->get('contao.security.token_checker')->isPreviewMode()) {
            $columns[] = "$t.published=?";
            $values[] = 1;
        }

        return static::findBy($columns, $values, ['order' => "$t.sorting"]);
    }

    /**
     * Find published category by ID or alias.
     *
     * @param string $idOrAlias
     */
    public static function findPublishedByIdOrAlias($idOrAlias): self|null
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
                $columns[] = "($t.alias=? OR translation.alias=?)";
                $values[] = $idOrAlias;
                $values[] = $idOrAlias;
            } else {
                $columns[] = "$t.alias=?";
                $values[] = $idOrAlias;
            }
        }

        if (!System::getContainer()->get('contao.security.token_checker')->isPreviewMode()) {
            $columns[] = "$t.published=?";
            $values[] = 1;
        }

        return static::findOneBy($columns, $values);
    }

    /**
     * Find published news categories.
     */
    public static function findPublished(): Collection|null
    {
        $t = static::getTableAlias();
        $options = ['order' => "$t.sorting"];

        if (System::getContainer()->get('contao.security.token_checker')->isPreviewMode()) {
            return static::findAll($options);
        }

        return static::findBy('published', 1, $options);
    }

    /**
     * Find published news categories by parent ID and IDs.
     */
    public static function findPublishedByIds(array $ids, int|null $pid = null): Collection|null
    {
        if (0 === \count($ids)) {
            return null;
        }

        $t = static::getTableAlias();
        $columns = ["$t.id IN (".implode(',', array_map('intval', $ids)).')'];
        $values = [];

        // Filter by pid
        if (null !== $pid) {
            $columns[] = "$t.pid=?";
            $values[] = $pid;
        }

        if (!System::getContainer()->get('contao.security.token_checker')->isPreviewMode()) {
            $columns[] = "$t.published=?";
            $values[] = 1;
        }

        return static::findBy($columns, $values, ['order' => "$t.sorting"]);
    }

    /**
     * Find published news categories by parent ID.
     *
     * @param int $pid
     */
    public static function findPublishedByPid($pid): Collection|null
    {
        $t = static::getTableAlias();
        $columns = ["$t.pid=?"];
        $values = [$pid];

        if (!System::getContainer()->get('contao.security.token_checker')->isPreviewMode()) {
            $columns[] = "$t.published=?";
            $values[] = 1;
        }

        return static::findBy($columns, $values, ['order' => "$t.sorting"]);
    }

    /**
     * Find the published categories by news.
     *
     * @return Collection|array<NewsCategoryModel>|null
     */
    public static function findPublishedByNews(array|int $newsId, array $arrOptions = []): Collection|array|null
    {
        if (0 === \count($ids = DcaRelationsModel::getRelatedValues('tl_news', 'categories', $newsId))) {
            return null;
        }

        $t = static::getTableAlias();
        $columns = ["$t.id IN (".implode(',', array_map('intval', array_unique($ids))).')'];
        $values = [];

        if (!System::getContainer()->get('contao.security.token_checker')->isPreviewMode()) {
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
    public static function getUsage(array $archives = [], int|null $category = null, $includeSubcategories = false, array $cumulativeCategories = [], $unionFiltering = false)
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

        if (!System::getContainer()->get('contao.security.token_checker')->isPreviewMode()) {
            $time = Date::floorToMinute();
            $columns[] = "$t.published=? AND ($t.start=? OR $t.start<=?) AND ($t.stop=? OR $t.stop>?)";
            $values = array_merge($values, [1, '', $time, '', $time]);
        }

        return NewsModel::countBy($columns, $values);
    }

    /**
     * Get all subcategory IDs.
     *
     * @return array
     */
    public static function getAllSubcategoriesIds($category)
    {
        $ids = Database::getInstance()->getChildRecords($category, static::$strTable, false, (array) $category, !System::getContainer()->get('contao.security.token_checker')->isPreviewMode() ? 'published=1' : '');
        $ids = array_map('intval', $ids);

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

        return static::findBy(["$t.id IN (".implode(',', array_map('intval', $arrIds)).')'], null);
    }

    /**
     * Get the table alias.
     *
     * @return string
     */
    public static function getTableAlias()
    {
        return static::$strTable;
    }
}
