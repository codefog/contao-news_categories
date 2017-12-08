<?php

/*
 * News Categories Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle;

use Codefog\NewsCategoriesBundle\Criteria\NewsCriteria;
use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Contao\News;

class FeedGenerator extends News
{
    /**
     * @inheritDoc
     */
    protected function generateFiles($arrFeed)
    {
        $arrArchives = \StringUtil::deserialize($arrFeed['archives']);

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

        $criteria = new NewsCriteria(\System::getContainer()->get('contao.framework'));
        $criteria->setBasicCriteria($arrArchives);

        // Filter by categories
        if (count($categories = \StringUtil::deserialize($arrFeed['categories'], true)) > 0) {
            $criteria->setDefaultCategories($categories);
        }

        // Set the limit
        if ($arrFeed['maxItems'] > 0) {
            $criteria->setLimit($arrFeed['maxItems']);
        }

        $objArticle = \NewsModel::findBy($criteria->getColumns(), $criteria->getValues(), $criteria->getOptions());

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
                        $arrUrls[$jumpTo] = $objParent->getAbsoluteUrl(\Config::get('useAutoItem') ? '/%s' : '/items/%s');
                    }
                }

                // Skip the event if it requires a jumpTo URL but there is none
                if ($arrUrls[$jumpTo] === false && $objArticle->source == 'default')
                {
                    continue;
                }

                $categories = [];

                // Get the categories
                if ($arrFeed['categories_show'] && ($categoryModels = NewsCategoryModel::findPublishedByNews($objArticle->id)) !== null) {
                    /** @var NewsCategoryModel $categoryModel */
                    foreach ($categoryModels as $categoryModel) {
                        $categories[] = $categoryModel->getTitle();
                    }
                }

                $strUrl = $arrUrls[$jumpTo];
                $objItem = new \FeedItem();

                $objItem->title = $objArticle->headline;
                $objItem->link = $this->getLink($objArticle, $strUrl);
                $objItem->published = $objArticle->date;

                /** @var \BackendUser $objAuthor */
                if (($objAuthor = $objArticle->getRelated('author')) !== null)
                {
                    $objItem->author = $objAuthor->name;
                }

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

                // Add the categories
                if (count($categories) > 0) {
                    switch ($arrFeed['categories_show']) {
                        case 'title':
                            $objItem->title = sprintf('[%s] %s', implode(', ', $categories), $objArticle->headline);
                            break;
                        case 'text_before':
                        case 'text_after':
                            $buffer = sprintf('<p>%s %s</p>', $GLOBALS['TL_LANG']['MSC']['newsCategories'], implode(', ', $categories));

                            if ($arrFeed['categories_show'] === 'text_before') {
                                $strDescription = $buffer . $strDescription;
                            } else {
                                $strDescription .= $buffer;
                            }
                            break;
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
                    $arrEnclosure = \StringUtil::deserialize($objArticle->enclosure, true);

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
        \File::putContent('web/share/' . $strFile . '.xml', $this->replaceInsertTags($objFeed->$strType(), false));
    }
}
