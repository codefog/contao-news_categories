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

    /**
     * Prepare the category object
     *
     * @param NewsCategoryModel $objCategory
     *
     * @return object
     */
    public static function prepareCategory(NewsCategoryModel $objCategory)
    {
        $category                = (object) $objCategory->row();
        $category->name          = $category->frontendTitle ?: $category->title;
        $category->class         = 'category_' . $category->id . ($category->cssClass ? (' ' . $category->cssClass) : '');
        $category->linkTitle     = specialchars($category->frontendTitle ?: $category->title);
        $category->href          = '';
        $category->hrefWithParam = '';
        $category->targetPage    = null;

        // Add the target page
        if (($targetPage = $objCategory->getTargetPage()) !== null)
        {
            $category->href          = $targetPage->getFrontendUrl();
            $category->hrefWithParam = $targetPage->getFrontendUrl('/' . NewsCategories::getParameterName() . '/' . $category->alias);
            $category->targetPage    = $targetPage;
        }

        // Add the news target page
        if (($targetPages = $objCategory->getNewsTargetPages()) !== null)
        {
            $targets = [];

            foreach ($targetPages as $news_archive => $targetPage)
            {
                if ($targetPage->category === null && $targetPage->news === null)
                {
                    continue;
                }

                $target = new \stdClass();

                if ($targetPage->category !== null)
                {
                    $target->categoryUrl          = $targetPage->category->getFrontendUrl();
                    $target->categoryUrlWithParam = $targetPage->category->getFrontendUrl('/' . NewsCategories::getParameterName() . '/' . $category->alias);
                    $target->categoryPage         = $targetPage->category;
                }

                if ($targetPage->news !== null)
                {
                    $target->categoryNewsUrl  = $targetPage->news->getFrontendUrl();
                    $target->categoryNewsPage = $targetPage->news;
                }

                $targets[$news_archive] = $target;
            }

            $category->newsTargets = $targets;
        }

        // Register a function to generate category URL manually
        $category->getUrl = function (\PageModel $page) use ($category) {
            return $category->getUrl($page);
        };

        $category->tree = CategoryHelper::getCategoryTree($objCategory->id);

        return $category;
    }

    /**
     * Get the category tree with all parent categories of the given category id
     *
     * @param integer $intId     The category id
     * @param bool    $max_level Maximum level of parent categories that should be covered, set to null for unlimited execution, 0 for only current category with its parent
     * @param array   $all       Required for recursion
     *
     * @return array|null
     */
    public static function getCategoryTree($intId, $max_level = null, $all = [])
    {
        if (!is_numeric($max_level))
        {
            $max_level = null;
        }

        $category = NewsCategoryModel::findPublishedByIdOrAlias($intId);

        if ($category === null)
        {
            return null;
        }

        $category = $category->current();

        // store parent within current category
        if (!empty($all))
        {
            $all[count($all) - 1]->parent = static::prepareCategory($category);

            if ($max_level !== null && $max_level <= count($all))
            {
                return array_reverse($all);  // sort in reverse order (parent to children)
            }
        }

        $all[] = $category;

        // no more parent category
        if ($category->pid == 0)
        {
            return array_reverse($all);  // sort in reverse order (parent to children)
        }

        return static::getCategoryTree($category->pid, $max_level, $all);
    }
}