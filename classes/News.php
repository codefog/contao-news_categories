<?php

/**
 * news_categories extension for Contao Open Source CMS
 *
 * Copyright (C) 2011-2014 Codefog
 *
 * @package news_categories
 * @author  Webcontext <http://webcontext.com>
 * @author  Codefog <info@codefog.pl>
 * @author  Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 * @license LGPL
 */

namespace NewsCategories;

/**
 * Provide methods regarding news archives
 */
class News extends \Contao\News
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

    /**
     * Parse news categories insert tags
     *
     * @param string $tag
     *
     * @return string|bool
     */
    public function parseCategoriesTags($tag)
    {
        $chunks = trimsplit('::', $tag);

        if ($chunks[0] === 'news_categories') {
            $className = \NewsCategories\NewsCategories::getModelClass();
            $param     = NewsCategories::getParameterName();

            if (($newsModel = $className::findPublishedByIdOrAlias(\Input::get($param))) !== null) {
                return $newsModel->{$chunks[1]};
            }
        }

        return false;
    }

    /**
     * Generate an XML files and save them to the root directory
     * @param array
     */
    protected function generateFiles($arrFeed)
    {
        $arrArchives = deserialize($arrFeed['archives']);

        if (!is_array($arrArchives) || empty($arrArchives))
        {
            return;
        }

        $strType = ($arrFeed['format'] == 'atom') ? 'generateAtom' : 'generateRss';
        $strLink = $arrFeed['feedBase'] ?: \Environment::get('base');
        $strFile = $arrFeed['feedName'];

        $objFeed = new \Feed($strFile);
        $objFeed->link = $strLink;
        $objFeed->title = $arrFeed['title'];
        $objFeed->description = $arrFeed['description'];
        $objFeed->language = $arrFeed['language'];
        $objFeed->published = $arrFeed['tstamp'];

        $arrCategories = deserialize($arrFeed['categories']);

        // Filter by categories
        if (is_array($arrCategories) && !empty($arrCategories)) {
            $GLOBALS['NEWS_FILTER_CATEGORIES'] = true;
            $GLOBALS['NEWS_FILTER_DEFAULT'] = $arrCategories;
        } else {
            $GLOBALS['NEWS_FILTER_CATEGORIES'] = false;
        }

        // Get the items
        if ($arrFeed['maxItems'] > 0)
        {
            $objArticle = \NewsModel::findPublishedByPids($arrArchives, null, $arrFeed['maxItems']);
        }
        else
        {
            $objArticle = \NewsModel::findPublishedByPids($arrArchives);
        }

        // Parse the items
        if ($objArticle !== null)
        {
            $arrUrls = array();

            while ($objArticle->next())
            {
                $jumpTo = $objArticle->getRelated('pid')->jumpTo;

                // No jumpTo page set (see #4784)
                if (!$jumpTo)
                {
                    continue;
                }

                // Get the jumpTo URL
                if (!isset($arrUrls[$jumpTo]))
                {
                    $objParent = \PageModel::findWithDetails($jumpTo);

                    // A jumpTo page is set but does no longer exist (see #5781)
                    if ($objParent === null)
                    {
                        $arrUrls[$jumpTo] = false;
                    }
                    else
                    {
                        $arrUrls[$jumpTo] = $objParent->getAbsoluteUrl((\Config::get('useAutoItem') && !\Config::get('disableAlias')) ? '/%s' : '/items/%s');
                    }
                }

                // Skip the event if it requires a jumpTo URL but there is none
                if ($arrUrls[$jumpTo] === false && $objArticle->source == 'default')
                {
                    continue;
                }

                // Get the categories
                if ($arrFeed['categories_show']) {
                    $arrCategories = array();

                    if (($objCategories = NewsCategoryModel::findPublishedByIds(deserialize($objArticle->categories, true))) !== null) {
                        $arrCategories = $objCategories->fetchEach('title');
                    }
                }

                $strUrl = $arrUrls[$jumpTo];
                $objItem = new \FeedItem();

                // Add the categories to the title
                if ($arrFeed['categories_show'] == 'title') {
                    $objItem->title = sprintf('[%s] %s', implode(', ', $arrCategories), $objArticle->headline);
                } else {
                    $objItem->title = $objArticle->headline;
                }

                $objItem->link = $this->getLink($objArticle, $strUrl);
                $objItem->published = $objArticle->date;
                $objItem->author = $objArticle->authorName;

                // Prepare the description
                if ($arrFeed['source'] == 'source_text')
                {
                    $strDescription = '';
                    $objElement = \ContentModel::findPublishedByPidAndTable($objArticle->id, 'tl_news');

                    if ($objElement !== null)
                    {
                        // Overwrite the request (see #7756)
                        $strRequest = \Environment::get('request');
                        \Environment::set('request', $objItem->link);

                        while ($objElement->next())
                        {
                            $strDescription .= $this->getContentElement($objElement->current());
                        }

                        \Environment::set('request', $strRequest);
                    }
                }
                else
                {
                    $strDescription = $objArticle->teaser;
                }

                // Add the categories to the description
                if ($arrFeed['categories_show'] == 'text_before' || $arrFeed['categories_show'] == 'text_after') {
                    $strCategories = '<p>' . $GLOBALS['TL_LANG']['MSC']['newsCategories'] . ' ' .  implode(', ', $arrCategories) . '</p>';

                    if ($arrFeed['categories_show'] == 'text_before') {
                        $strDescription = $strCategories . $strDescription;
                    } else {
                        $strDescription .= $strCategories;
                    }
                }

                $strDescription = $this->replaceInsertTags($strDescription, false);
                $objItem->description = $this->convertRelativeUrls($strDescription, $strLink);

                // Add the article image as enclosure
                if ($objArticle->addImage)
                {
                    $objFile = \FilesModel::findByUuid($objArticle->singleSRC);

                    if ($objFile !== null)
                    {
                        $objItem->addEnclosure($objFile->path, $strLink);
                    }
                }

                // Enclosures
                if ($objArticle->addEnclosure)
                {
                    $arrEnclosure = deserialize($objArticle->enclosure, true);

                    if (is_array($arrEnclosure))
                    {
                        $objFile = \FilesModel::findMultipleByUuids($arrEnclosure);

                        if ($objFile !== null)
                        {
                            while ($objFile->next())
                            {
                                $objItem->addEnclosure($objFile->path, $strLink);
                            }
                        }
                    }
                }

                $objFeed->addItem($objItem);
            }
        }

        // Create the file
        if (class_exists('Contao\CoreBundle\ContaoCoreBundle')) {
            \File::putContent('web/share/'.$strFile.'.xml', $this->replaceInsertTags($objFeed->$strType(), false));
        } else {
            \File::putContent('share/'.$strFile.'.xml', $this->replaceInsertTags($objFeed->$strType(), false));
        }
    }
}
