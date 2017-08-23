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

use HeimrichHannot\FieldPalette\FieldPaletteModel;

/**
 * Reads and writes news categories
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
        if (!$pageId)
        {
            $pid = $this->pid;

            do
            {
                $parent = static::findByPk($pid);

                if ($parent !== null)
                {
                    $pid    = $parent->pid;
                    $pageId = $parent->jumpTo;
                }
            } while ($pid && !$pageId);
        }

        return \PageModel::findByPk($pageId);
    }

    /**
     * Get the target pages by news_archives
     *
     * @return array|null
     */
    public function getTargetPages()
    {
        if (($references = FieldPaletteModel::findPublishedByPidAndTableAndField($this->id, 'tl_news_category', 'jumpTo')) === null)
        {
            return null;
        }

        $pages = [];

        while ($references->next())
        {
            $target = new \stdClass();

            if ($references->news_category_jumpTo > 0)
            {
                $target->category = \PageModel::findByPk($references->news_category_jumpTo);
            }

            if ($references->news_category_news_jumpTo > 0)
            {
                $target->news = \PageModel::findByPk($references->news_category_news_jumpTo);
            }

            $pages[$references->news_category_news_archive] = $target;
        }

        return $pages;
    }

    /**
     * Find published news categories by their archives
     *
     * @param array $arrArchives An array of archives
     * @param array $arrIds      An array of categories
     *
     * @return \Model|null The NewsModelCategpry or null if there are no categories
     */
    public static function findPublishedByParent($arrArchives, $arrIds = [])
    {
        if (!is_array($arrArchives) || empty($arrArchives))
        {
            return null;
        }

        $time       = time();
        $t          = static::$strTable;
        $arrColumns = [
            "$t.id IN (SELECT category_id FROM tl_news_categories WHERE news_id IN (SELECT id FROM tl_news WHERE pid IN (" . implode(',', array_map('intval', $arrArchives)) . ")"
            . (!BE_USER_LOGGED_IN ? " AND (tl_news.start='' OR tl_news.start<$time) AND (tl_news.stop='' OR tl_news.stop>$time) AND tl_news.published=1" : "") . "))",
        ];

        // Filter by custom categories
        if (is_array($arrIds) && !empty($arrIds))
        {
            $arrColumns[] = "$t.id IN (" . implode(',', array_map('intval', $arrIds)) . ")";
        }

        if (!BE_USER_LOGGED_IN)
        {
            $arrColumns[] = "$t.published=1";
        }

        return static::findBy($arrColumns, null, ['order' => "$t.sorting"]);
    }

    /**
     * Find published category by ID or alias
     *
     * @param mixed $varId The numeric ID or alias name
     *
     * @return \Model|null The NewsCategoryModel or null if there is no category
     */
    public static function findPublishedByIdOrAlias($varId)
    {
        $t          = static::$strTable;
        $arrColumns = ["($t.id=? OR $t.alias=?)"];

        if (!BE_USER_LOGGED_IN)
        {
            $arrColumns[] = "$t.published=1";
        }

        return static::findBy($arrColumns, [(is_numeric($varId) ? $varId : 0), $varId]);
    }

    /**
     * Find published categories by IDs
     *
     * @param array $arrIds An array of category IDs
     *
     * @return \Model|null The NewsCategoryModel or null if there is no category
     */
    public static function findPublishedByIds($arrIds)
    {
        if (!is_array($arrIds) || empty($arrIds))
        {
            return null;
        }

        $t          = static::$strTable;
        $arrColumns = ["$t.id IN (" . implode(',', array_map('intval', $arrIds)) . ")"];

        if (!BE_USER_LOGGED_IN)
        {
            $arrColumns[] = "$t.published=1";
        }

        return static::findBy($arrColumns, null, ['order' => "$t.sorting"]);
    }

    /**
     * Find published news categories by parent ID and IDs
     *
     * @param integer $intPid The parent ID
     * @param array   $arrIds An array of categories
     *
     * @return \Model|null The NewsModelCategpry or null if there are no categories
     */
    public static function findPublishedByPidAndIds($intPid, $arrIds)
    {
        if (!is_array($arrIds) || empty($arrIds))
        {
            return null;
        }

        $objCategories = \Database::getInstance()->prepare(
            "SELECT c1.*, (SELECT COUNT(*) FROM tl_news_category c2 WHERE c2.pid=c1.id AND c2.id IN (" . implode(',', array_map('intval', $arrIds)) . ")"
            . (!BE_USER_LOGGED_IN ? " AND c2.published=1" : "") . ") AS subcategories FROM tl_news_category c1 WHERE c1.pid=? AND c1.id IN (" . implode(
                ',',
                array_map('intval', $arrIds)
            ) . ")" . (!BE_USER_LOGGED_IN ? " AND c1.published=1" : "") . " ORDER BY c1.sorting"
        )->execute($intPid);

        if ($objCategories->numRows < 1)
        {
            return null;
        }

        return \Model\Collection::createFromDbResult($objCategories, static::$strTable);
    }
}
