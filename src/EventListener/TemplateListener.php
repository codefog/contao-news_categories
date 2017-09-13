<?php

namespace Codefog\NewsCategoriesBundle\EventListener;

class TemplateListener
{
    /**
     * Add the categories to the template
     * @param object
     * @param array
     * @param object $module
     */
    public function addCategoriesToTemplate($objTemplate, $arrData, $module)
    {
        if (isset($arrData['categories'])) {
            $arrCategories = array();
            $arrCategoriesList = array();
            $categories = deserialize($arrData['categories']);

            if (is_array($categories) && !empty($categories)) {
                $strClass = \NewsCategories\NewsCategories::getModelClass();
                $objCategories = $strClass::findPublishedByIds($categories);

                // Add the categories to template
                if ($objCategories !== null) {
                    /** @var NewsCategoryModel $objCategory */
                    foreach ($objCategories as $objCategory) {
                        // Skip the category in news list or archive module
                        if (($module instanceof \ModuleNewsList || $module instanceof \ModuleNewsArchive)
                            && $objCategory->hideInList
                        ) {
                            continue;
                        }

                        // Skip the category in the news reader module
                        if ($module instanceof \ModuleNewsReader && $objCategory->hideInReader) {
                            continue;
                        }

                        $strName = $objCategory->frontendTitle ? $objCategory->frontendTitle : $objCategory->title;

                        $arrCategories[$objCategory->id] = $objCategory->row();
                        $arrCategories[$objCategory->id]['name'] = $strName;
                        $arrCategories[$objCategory->id]['class'] = 'category_' . $objCategory->id . ($objCategory->cssClass ? (' ' . $objCategory->cssClass) : '');
                        $arrCategories[$objCategory->id]['linkTitle'] = specialchars($strName);
                        $arrCategories[$objCategory->id]['href'] = '';
                        $arrCategories[$objCategory->id]['hrefWithParam'] = '';
                        $arrCategories[$objCategory->id]['targetPage'] = null;

                        // Add the target page
                        if (($targetPage = $objCategory->getTargetPage()) !== null) {
                            $arrCategories[$objCategory->id]['href'] = $targetPage->getFrontendUrl();
                            $arrCategories[$objCategory->id]['hrefWithParam'] = $targetPage->getFrontendUrl('/' . NewsCategories::getParameterName() . '/' . $objCategory->alias);
                            $arrCategories[$objCategory->id]['targetPage'] = $targetPage;
                        }

                        // Register a function to generate category URL manually
                        $arrCategories[$objCategory->id]['getUrl'] = function(\PageModel $page) use ($objCategory) {
                            return $objCategory->getUrl($page);
                        };

                        // Generate categories list
                        $arrCategoriesList[$objCategory->id] = $strName;
                    }

                    // Sort the category list alphabetically
                    asort($arrCategoriesList);

                    // Sort the categories alphabetically
                    uasort($arrCategories, function($a, $b) {
                        return strnatcasecmp($a['name'], $b['name']);
                    });
                }
            }

            $objTemplate->categories = $arrCategories;
            $objTemplate->categoriesList = $arrCategoriesList;
        }
    }
}
