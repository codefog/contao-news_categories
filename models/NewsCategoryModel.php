<?php

/**
 * news_categories extension for Contao Open Source CMS
 *
 * Copyright (C) 2011-2014 Codefog
 *
 * @package news_categories
 * @link    http://codefog.pl
 * @author  Webcontext <http://webcontext.com>
 * @author  Codefog <info@codefog.pl>
 * @author  Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 * @license LGPL
 */

namespace NewsCategories;

use HeimrichHannot\FieldpaletteBundle\Model\FieldPaletteModel;

/**
 * Reads and writes news categories
 *
 * @property integer $id
 * @property integer $pid
 * @property integer $sorting
 * @property integer $tstamp
 * @property string $title
 * @property string $frontendTitle
 * @property string $alias
 * @property string $cssClass
 * @property boolean $hideInList
 * @property boolean $hideInReader
 * @property boolean $excludeInRelated
 * @property string $jumpTo
 * @property string $archiveConfig
 * @property boolean $published
 *
 * @method static NewsCategoryModel|null findById($id, array $opt = [])
 * @method static NewsCategoryModel|null findByPk($id, array $opt = [])
 * @method static NewsCategoryModel|null findByIdOrAlias($val, array $opt = [])
 * @method static NewsCategoryModel|null findOneBy($col, $val, array $opt = [])
 * @method static NewsCategoryModel|null findOneBySorting($val, array $opt = [])
 * @method static NewsCategoryModel|null findOneByTstamp($val, array $opt = [])
 * @method static NewsCategoryModel|null findOneByTitle($val, array $opt = [])
 * @method static NewsCategoryModel|null findOneByFrontendTitle($val, array $opt = [])
 * @method static NewsCategoryModel|null findOneByAlias($val, array $opt = [])
 * @method static NewsCategoryModel|null findOneByCssClass($val, array $opt = [])
 * @method static NewsCategoryModel|null findOneByHideInList($val, array $opt = [])
 * @method static NewsCategoryModel|null findOneByHideInReader($val, array $opt = [])
 * @method static NewsCategoryModel|null findOneByExcludeInRelated($val, array $opt = [])
 * @method static NewsCategoryModel|null findOneByJumpTo($val, array $opt = [])
 * @method static NewsCategoryModel|null findOneByArchiveConfig($val, array $opt = [])
 * @method static NewsCategoryModel|null findOneByPublished($val, array $opt = [])
 *
 * @method static \Model\Collection|NewsCategoryModel[]|NewsCategoryModel|null findByPid($val, array $opt = [])
 * @method static \Model\Collection|NewsCategoryModel[]|NewsCategoryModel|null findBySorting($val, array $opt = [])
 * @method static \Model\Collection|NewsCategoryModel[]|NewsCategoryModel|null findByTstamp($val, array $opt = [])
 * @method static \Model\Collection|NewsCategoryModel[]|NewsCategoryModel|null findByTitle($val, array $opt = [])
 * @method static \Model\Collection|NewsCategoryModel[]|NewsCategoryModel|null findByFrontendTitle($val, array $opt = [])
 * @method static \Model\Collection|NewsCategoryModel[]|NewsCategoryModel|null findByAlias($val, array $opt = [])
 * @method static \Model\Collection|NewsCategoryModel[]|NewsCategoryModel|null findByCssClass($val, array $opt = [])
 * @method static \Model\Collection|NewsCategoryModel[]|NewsCategoryModel|null findByHideInList($val, array $opt = [])
 * @method static \Model\Collection|NewsCategoryModel[]|NewsCategoryModel|null findByHideInReader($val, array $opt = [])
 * @method static \Model\Collection|NewsCategoryModel[]|NewsCategoryModel|null findByExcludeInRelated($val, array $opt = [])
 * @method static \Model\Collection|NewsCategoryModel[]|NewsCategoryModel|null findByJumpTo($val, array $opt = [])
 * @method static \Model\Collection|NewsCategoryModel[]|NewsCategoryModel|null findByArchiveConfig($val, array $opt = [])
 * @method static \Model\Collection|NewsCategoryModel[]|NewsCategoryModel|null findByPublished($val, array $opt = [])
 * @method static \Model\Collection|NewsCategoryModel[]|NewsCategoryModel|null findMultipleByIds($val, array $opt = [])
 * @method static \Model\Collection|NewsCategoryModel[]|NewsCategoryModel|null findBy($col, $val, array $opt = [])
 * @method static \Model\Collection|NewsCategoryModel[]|NewsCategoryModel|null findAll(array $opt = [])
 *
 * @method static integer countById($id, array $opt = [])
 * @method static integer countByPid($val, array $opt = [])
 * @method static integer countBySorting($val, array $opt = [])
 * @method static integer countByTstamp($val, array $opt = [])
 * @method static integer countByTitle($val, array $opt = [])
 * @method static integer countByFrontendTitle($val, array $opt = [])
 * @method static integer countByAlias($val, array $opt = [])
 * @method static integer countByCssClass($val, array $opt = [])
 * @method static integer countByHideInList($val, array $opt = [])
 * @method static integer countByHideInReader($val, array $opt = [])
 * @method static integer countByExcludeInRelated($val, array $opt = [])
 * @method static integer countByJumpTo($val, array $opt = [])
 * @method static integer countByArchiveConfig($val, array $opt = [])
 * @method static integer countByPublished($val, array $opt = [])
 *
 * @author Rico Kaltofen <https://github.com/heimrichhannot>
 */
class NewsCategoryModel extends \Model
{

    /**
     * Table name
     *
     * @var string
     */
    protected static $strTable = 'tl_news_category';

    /**
     * Get the category URL
     *
     * @param \PageModel $page
     *
     * @return string
     */
    public function getUrl(\PageModel $page)
    {
        $page->loadDetails();

        return $page->getFrontendUrl('/' . NewsCategories::getParameterName($page->rootId) . '/' . $this->alias);
    }

    /**
     * Get the target page
     *
     * @return \PageModel|null
     */
    public function getTargetPage()
    {
        $pageId = $this->jumpTo;

        // Inherit the page from parent if there is none set
        if (!$pageId) {
            $pid = $this->pid;

            do {
                if (!$pid) {
                    $parent = null;
                    break;
                }

                $parent = static::findByPk($pid);

                if ($parent !== null) {
                    $pid    = $parent->pid;
                    $pageId = $parent->jumpTo;
                }
            } while ($pid && !$pageId);
        }

        return $pageId > 0 ? \PageModel::findByPk($pageId) : null;
    }

    /**
     * Get the config by news_archive with target pages and teaser
     *
     * @return array|null
     */
    public function getNewsCategoryConfig()
    {
        static $cache;

        if (is_array($cache) && isset($cache[$this->id])) {
            return $cache[$this->id];
        }

        if (($references = FieldPaletteModel::findPublishedByPidAndTableAndField($this->id, 'tl_news_category', 'archiveConfig')) === null) {
            return null;
        }

        $pages = [];

        while ($references->next()) {
            $target = [];

            if ($references->news_category_jumpTo > 0) {
                $target['category'] = \PageModel::findByPk($references->news_category_jumpTo);
            }

            if ($references->news_category_news_jumpTo > 0) {
                $target['news'] = \PageModel::findByPk($references->news_category_news_jumpTo);
            }

            $target['hasTeaser'] = false;

            if ($references->news_category_teaser != '') {
                $target['hasTeaser'] = true;
                $target['teaser']    = \StringUtil::toHtml5($references->news_category_teaser);
                $target['teaser']    = \StringUtil::encodeEmail($references->news_category_teaser);
            }

            $pages[$references->news_category_news_archive] = $target;
        }

        $cache[$this->id] = $pages;

        return $cache[$this->id];
    }

    /**
     * Find published news categories by their archives
     *
     * @param array $arrArchives An array of archives
     * @param array $arrIds An array of categories
     *
     * @return \Model\Collection|NewsCategoryModel[]|NewsCategoryModel|null A collection of models or null if there are no categories
     */
    public static function findPublishedByParent($arrArchives, $arrIds = [])
    {
        if (!is_array($arrArchives) || empty($arrArchives)) {
            return null;
        }

        $time       = time();
        $t          = static::$strTable;
        $arrColumns = [
            "$t.id IN (SELECT category_id FROM tl_news_categories WHERE news_id IN (SELECT id FROM tl_news WHERE pid IN (" . implode(',', array_map('intval', $arrArchives)) . ")"
            . (!BE_USER_LOGGED_IN ? " AND (tl_news.start='' OR tl_news.start<$time) AND (tl_news.stop='' OR tl_news.stop>$time) AND tl_news.published=1" : "") . "))",
        ];

        // Filter by custom categories
        if (is_array($arrIds) && !empty($arrIds)) {
            $arrColumns[] = "$t.id IN (" . implode(',', array_map('intval', $arrIds)) . ")";
        }

        if (!BE_USER_LOGGED_IN) {
            $arrColumns[] = "$t.published=1";
        }

        return static::findBy($arrColumns, null, ['order' => "$t.sorting"]);
    }

    /**
     * Find published category by ID or alias
     *
     * @param mixed $varId The numeric ID or alias name
     *
     * @return NewsCategoryModel|null The NewsCategoryModel or null if there is no category
     */
    public static function findPublishedByIdOrAlias($varId)
    {
        $t          = static::$strTable;
        $arrColumns = ["($t.id=? OR $t.alias=?)"];

        if (!BE_USER_LOGGED_IN) {
            $arrColumns[] = "$t.published=1";
        }

        return static::findOneBy($arrColumns, [(is_numeric($varId) ? $varId : 0), $varId]);
    }

    /**
     * Find published categories by IDs
     *
     * @param array $arrIds An array of category IDs
     *
     * @return \Model\Collection|NewsCategoryModel[]|NewsCategoryModel|null A collection of models or null if there are no categories
     */
    public static function findPublishedByIds($arrIds)
    {
        if (!is_array($arrIds) || empty($arrIds)) {
            return null;
        }

        $t          = static::$strTable;
        $arrColumns = ["$t.id IN (" . implode(',', array_map('intval', $arrIds)) . ")"];

        if (!BE_USER_LOGGED_IN) {
            $arrColumns[] = "$t.published=1";
        }

        return static::findBy($arrColumns, null, ['order' => "$t.sorting"]);
    }

    /**
     * Find published news categories by parent ID and IDs
     *
     * @param integer $intPid The parent ID
     * @param array $arrIds An array of categories
     *
     * @return \Model\Collection|NewsCategoryModel[]|NewsCategoryModel|null A collection of models or null if there are no categories
     */
    public static function findPublishedByPidAndIds($intPid, $arrIds)
    {
        if (!is_array($arrIds) || empty($arrIds)) {
            return null;
        }

        $objCategories = \Database::getInstance()->prepare(
            "SELECT c1.*, (SELECT COUNT(*) FROM tl_news_category c2 WHERE c2.pid=c1.id AND c2.id IN (" . implode(',', array_map('intval', $arrIds)) . ")"
            . (!BE_USER_LOGGED_IN ? " AND c2.published=1" : "") . ") AS subcategories FROM tl_news_category c1 WHERE c1.pid=? AND c1.id IN (" . implode(
                ',',
                array_map('intval', $arrIds)
            ) . ")" . (!BE_USER_LOGGED_IN ? " AND c1.published=1" : "") . " ORDER BY c1.sorting"
        )->execute($intPid);

        if ($objCategories->numRows < 1) {
            return null;
        }

        return \Model\Collection::createFromDbResult($objCategories, static::$strTable);
    }
}
