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

class CategoryHelper
{
    private static $treeCache;

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

        // Add the target page
        if (($targetPage = $objCategory->getTargetPage()) !== null) {
            $category['href']          = $targetPage->getFrontendUrl();
            $category['hrefWithParam'] = $targetPage->getFrontendUrl('/' . NewsCategories::getParameterName() . '/' . $category['alias']);
            $category['targetPage']    = $targetPage;
        }

        // Add the news target page
        if (($targetPages = $objCategory->getNewsTargetPages()) !== null) {
            $targets = [];

            foreach ($targetPages as $news_archive => $targetPage) {
                if ($targetPage['category'] === null && $targetPage['news'] === null) {
                    continue;
                }

                $target = [];

                /**
                 * @var \PageModel $targetCategoryPage
                 */
                if (($targetCategoryPage = $targetPage['category']) !== null) {
                    $target['categoryHref']          = $targetCategoryPage->getFrontendUrl();
                    $target['categoryHrefWithParam'] = $targetCategoryPage->getFrontendUrl('/' . NewsCategories::getParameterName() . '/' . $category['alias']);
                    $target['categoryPage']          = $targetCategoryPage;
                }

                /**
                 * @var \PageModel $targetNewsPage
                 */
                if (($targetNewsPage = $targetPage['news']) !== null) {
                    $target['categoryNewsHref'] = $targetNewsPage->getFrontendUrl();
                    $target['categoryNewsPage'] = $targetNewsPage;
                }

                $targets[$news_archive] = $target;
            }

            $category['newsTargets'] = $targets;
        }

        // Register a function to generate category URL manually
        $category['getUrl'] = function (\PageModel $page) use ($objCategory) {
            return $objCategory->getUrl($page);
        };

        $category['tree'] = CategoryHelper::getCategoryTree($category['id']);

        return $category;
    }

    /**
     * Get the category tree with all parent categories of the given category id
     *
     * @param integer $intId The category id
     * @param integer|null $max_level Maximum level of parent categories that should be covered, set to null for unlimited execution, 0 for only current category with its parent
     * @param array $all Required for recursion
     *
     * @return array|null
     */
    public static function getCategoryTree($intId, $max_level = null, $all = [], $cacheKey = null)
    {
        $count = count($all);

        if ($count === 0) {
            $cacheKey = $intId;
        }

        // try to load from cache
        if ($cacheKey !== null && isset(static::$treeCache[$cacheKey]) && ($max_level === null || $count >= $max_level)) {
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

            if ($max_level !== null && $max_level <= $count) {
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

        return static::getCategoryTree($category->pid, $max_level, $all, $cacheKey);
    }
}