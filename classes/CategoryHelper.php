<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace NewsCategories;

use HeimrichHannot\FieldPalette\FieldPaletteModel;

class CategoryHelper
{
    private static $newsUrlCache;

    private static $treeCache;

    private static $idTreeCache;

    private static $flatIdTreeCache;

    /**
     * Get the news url based on the category
     * @param \Contao\NewsModel|\Contao\Model\Collection $objNews The news item
     *
     * @return string|null The news url based on the news archive and primary category or null if category has no news archive related jump to
     */
    public static function getCategoryNewsUrl($objNews)
    {
        if (($primaryCategory = static::getPrimaryNewsCategory($objNews)) === null) {
            return null;
        }

        $cacheKey = 'id_' . $objNews->id . '_' . $primaryCategory;

        // Load the URL from cache
        if (isset(self::$newsUrlCache[$cacheKey])) {
            return self::$newsUrlCache[$cacheKey];
        }

        if (($categoryNewsPage = CategoryHelper::getCategoryNewsPage($primaryCategory, $objNews->pid)) === null) {
            return null;
        }

        self::$newsUrlCache[$cacheKey] = ampersand(
            $categoryNewsPage->getAbsoluteUrl(
                ((\Config::get('useAutoItem') && !\Config::get('disableAlias')) ? '/' : '/items/') . ((!\Config::get('disableAlias')
                    && $objNews->alias != '') ? $objNews->alias : $objNews->id)
            )
        );

        return self::$newsUrlCache[$cacheKey];
    }

    /**
     * Get the primary news category
     *
     * @param \Contao\NewsModel|\Contao\Model\Collection $objNews The news item
     * @return int|null The category id or null if none is set
     */
    public static function getPrimaryNewsCategory($objNews)
    {
        $categories = deserialize($objNews->categories, true);

        // get from primary category or use first category as primary if not more than 1 category isset
        return $objNews->primaryCategory ?: ((count($categories) === 1) ? $categories[0] : null);
    }

    /**
     * Get the news target page based on the news archive or news category to news archive related jumpTo page
     * @param int $intCategory The category id
     * @param int $intArchive The news archive id
     *
     * @return \Contao\PageModel|null The page model based on the category to news archive relation or null
     */
    public static function getCategoryNewsPage($intCategory, $intArchive)
    {
        if (!$intCategory || !$intArchive) {
            return null;
        }

        if (($objCategory = NewsCategoryModel::findPublishedByIdOrAlias($intCategory)) === null) {
            return null;
        }

        $category = CategoryHelper::prepareCategory($objCategory);

        if ($category === null || !is_array($category['archives']) || !isset($category['archives'][$intArchive])) {
            return null;
        }

        return $category['archives'][$intArchive]['categoryNewsPage'];
    }

    /**
     * Get news archive category page by category id and news archive id
     * @param int $idCategory The category id
     * @param int $idArchive The news archive id
     *
     * @return \Contao\PageModel|null The news archive to category related page model
     */
    public static function getNewsArchiveCategoryPage($idCategory, $idArchive)
    {
        if (($references = static::getNewsArchiveCategoryConfig($idCategory, $idArchive)) !== null) {
            return \PageModel::findPublishedById($references->news_category_news_jumpTo);
        }

        return null;
    }

    /**
     * Get news archive category configuration
     * @param int $idCategory The category id
     * @param int $idArchive The news archive id
     *
     * @return FieldPaletteModel|null The config model
     */
    public static function getNewsArchiveCategoryConfig($idCategory, $idArchive)
    {
        return FieldPaletteModel::findPublishedByPidAndTableAndField($idCategory, 'tl_news_category', 'archiveConfig', ['limit' => 1], ['news_category_news_archive = ?'], [$idArchive]);
    }

    /**
     * Prepare the category object
     *
     * @param NewsCategoryModel $objCategory
     *
     * @return object
     */
    public static function prepareCategory(NewsCategoryModel $objCategory)
    {
        $category                  = $objCategory->row();
        $category['name']          = $category['frontendTitle'] ?: $category['title'];
        $category['class']         = 'category_' . $category->id . ($category['cssClass'] ? (' ' . $category['cssClass']) : '');
        $category['linkTitle']     = specialchars($category['name']);
        $category['href']          = '';
        $category['hrefWithParam'] = '';
        $category['targetPage']    = null;
        $category['hasTeaser']     = false;

        // Clean the RTE output
        if ($category['teaser'] != '') {
            $category['hasTeaser'] = true;
            $category['teaser']    = \StringUtil::toHtml5($category['teaser']);
            $category['teaser']    = \StringUtil::encodeEmail($category['teaser']);
        }

        // Add the target page
        if (($targetPage = $objCategory->getTargetPage()) !== null) {
            $category['href']          = $targetPage->getFrontendUrl();
            $category['hrefWithParam'] = $targetPage->getFrontendUrl('/' . NewsCategories::getParameterName() . '/' . $category['alias']);
            $category['targetPage']    = $targetPage;
        }

        // Add the news target page
        if (($archiveConfigs = $objCategory->getNewsCategoryConfig()) !== null) {
            $archives = [];

            foreach ($archiveConfigs as $news_archive => $archiveConfig) {
                $archive = [];

                /**
                 * @var \PageModel $targetCategoryPage
                 */
                if (($targetCategoryPage = $archiveConfig['category']) !== null) {
                    $archive['categoryHref']          = $targetCategoryPage->getFrontendUrl();
                    $archive['categoryHrefWithParam'] = $targetCategoryPage->getFrontendUrl('/' . NewsCategories::getParameterName() . '/' . $category['alias']);
                    $archive['categoryPage']          = $targetCategoryPage;
                }

                /**
                 * @var \PageModel $targetNewsPage
                 */
                if (($targetNewsPage = $archiveConfig['news']) !== null) {
                    $archive['categoryNewsHref'] = $targetNewsPage->getFrontendUrl();
                    $archive['categoryNewsPage'] = $targetNewsPage;
                }

                if ($archiveConfig['hasTeaser']) {
                    $archive['hasTeaser'] = true;
                    $archive['teaser']    = $archiveConfig['teaser'];
                }

                if (empty($archive)) {
                    continue;
                }

                $archives[$news_archive] = $archive;
            }

            $category['archives'] = $archives;
        }

        // Register a function to generate category URL manually
        $category['getUrl'] = function (\PageModel $page) use ($objCategory) {
            return $objCategory->getUrl($page);
        };

        // Register a function to provide the category tree for the current category on demand
        $category['getTree'] = function () use ($objCategory) {
            return CategoryHelper::getCategoryTree($objCategory->id);
        };

        return $category;
    }

    /**
     * Get the categories id as tree cache and return it as array
     *
     * @param int $pid The parent id
     * @param int $maxLevel The max level of child categories that should be covered descending from $pid
     * @param boolean $flat Set true if the tree should be returned as flat array
     * @param int $level Current recursion level
     * @param integer $cacheKey Required to load from cache within recursion
     *
     * @return array All categories as tree (key = category id) or flat (value = category id)
     */
    public static function getCategoryIdTree($pid = 0, $maxLevel = 1, $flat = false, $level = 0, $cacheKey = null)
    {
        $pid        = intval($pid);
        $isParent   = false;
        $categories = [];

        if ($cacheKey === null) {
            $isParent = true;
            $cacheKey = $pid . '_' . $maxLevel;

            if (is_array(static::$idTreeCache) && !$flat && isset(static::$idTreeCache[$cacheKey])) {
                return static::$idTreeCache[$cacheKey];
            } elseif (is_array(static::$flatIdTreeCache) && $flat && isset(static::$flatIdTreeCache[$cacheKey])) {
                return static::$flatIdTreeCache[$cacheKey];
            }
        }


        $objCategories = NewsCategoryModel::findByPid($pid, ['order' => 'pid, sorting']);

        if ($objCategories === null) {
            return $categories;
        }

        while ($objCategories->next()) {
            $nested = [];

            if ($level <= $maxLevel) {
                $nested = static::getCategoryIdTree($objCategories->id, $maxLevel, $flat, ++$level, $cacheKey);
            }

            if (empty($nested) || $level >= $maxLevel) {
                $level = 0;
            }

            if ($flat) {
                $categories = array_merge($categories, [intval($objCategories->id)], $nested);
                continue;
            }

            $categories[intval($objCategories->id)] = $nested;
        }

        if ($isParent) {
            if ($flat) {
                static::$flatIdTreeCache[$cacheKey] = $categories;
                array_unshift(static::$flatIdTreeCache[$cacheKey], $pid);
                return static::$flatIdTreeCache[$cacheKey];
            } else {
                static::$idTreeCache[$cacheKey][$pid] = $categories;
                return static::$idTreeCache[$cacheKey];
            }
        }

        return $categories;
    }

    /**
     * Get the category tree with all parent categories of the given category id
     *
     * @param integer $intId The category id
     * @param integer|null $maxLevel Maximum level of parent categories that should be covered, set to null for unlimited execution, 0 for only current category with its parent
     * @param array $all Required for recursion
     * @param integer $cacheKey Required to load from cache within recursion
     *
     * @return array|null
     */
    public static function getCategoryTree($intId, $maxLevel = null, $all = [], $cacheKey = null)
    {
        $count = count($all);

        if ($count === 0) {
            $cacheKey = $intId;

            if ($maxLevel !== null) {
                $cacheKey .= '_' . $maxLevel;
            }
        }

        // try to load from cache
        if ($cacheKey !== null && isset(static::$treeCache[$cacheKey]) && ($maxLevel === null || $count >= $maxLevel)) {
            return static::$treeCache[$cacheKey];
        }

        $category = NewsCategoryModel::findPublishedByIdOrAlias($intId);

        if ($category === null) {
            return null;
        }

        $category = $category->current();

        // store parent within current category
        if ($count > 0) {
            $all[key($all)]->parent = static::prepareCategory($category);

            if ($maxLevel !== null && $maxLevel <= $count) {
                $tree                         = array_reverse($all, true);
                static::$treeCache[$cacheKey] = $tree;
                return $tree;  // sort in reverse order (parent to children)
            }
        }

        $all[$category->id] = $category;

        // no more parent category
        if ((int)$category->pid === 0) {
            $tree                         = array_reverse($all, true);
            static::$treeCache[$cacheKey] = $tree;
            return $tree;  // sort in reverse order (parent to children)
        }

        return static::getCategoryTree($category->pid, $maxLevel, $all, $cacheKey);
    }
}