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
use Codefog\NewsCategoriesBundle\Exception\NoNewsException;
use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Contao\Config;
use Contao\Controller;
use Contao\News;
use Contao\PageModel;
use Contao\System;
use Contao\UserModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class FeedGenerator extends News
{
    /**
     * Page cache array
     * @var array
     */
    private static $arrPageCache = array();

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

        $container = System::getContainer();
        $criteria = new NewsCriteria($container->get('contao.framework'));

        try {
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
        } catch (NoNewsException $e) {
            $objArticle = null;
        }

        // Parse the items
        if ($objArticle !== null)
        {
            $arrUrls = array();

            /** @var RequestStack $requestStack */
            $requestStack = $container->get('request_stack');
            $currentRequest = $requestStack->getCurrentRequest();

            $time = time();
            $origObjPage = $GLOBALS['objPage'] ?? null;

            while ($objArticle->next())
            {
                // Never add unpublished elements to the RSS feeds
                if (!$objArticle->published || ($objArticle->start && $objArticle->start > $time) || ($objArticle->stop && $objArticle->stop <= $time))
                {
                    continue;
                }

                $jumpTo = $objArticle->getRelated('pid')->jumpTo;

                // No jumpTo page set (see #4784)
                if (!$jumpTo)
                {
                    continue;
                }

                $objParent = $this->getPageWithDetails($jumpTo);

                // A jumpTo page is set but does no longer exist (see #5781)
                if ($objParent === null)
                {
                    continue;
                }

                // Override the global page object (#2946)
                $GLOBALS['objPage'] = $objParent;

                // Get the jumpTo URL
                if (!isset($arrUrls[$jumpTo]))
                {
                    $arrUrls[$jumpTo] = $objParent->getAbsoluteUrl(Config::get('useAutoItem') ? '/%s' : '/items/%s');
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

                // Push a new request to the request stack (#3856)
                $request = $this->createSubRequest($objItem->link, $currentRequest);
                $request->attributes->set('_scope', 'frontend');
                $requestStack->push($request);

                /** @var UserModel $objAuthor */
                if (($objAuthor = $objArticle->getRelated('author')) instanceof UserModel)
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
                    $strDescription = $objArticle->teaser ?? '';
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

                if ($container->has('contao.insert_tag.parser')) {
                    $strDescription = $container->get('contao.insert_tag.parser')->replaceInline($strDescription);
                } else {
                    $strDescription = Controller::replaceInsertTags($strDescription);
                }

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

                $requestStack->pop();
            }

            $GLOBALS['objPage'] = $origObjPage;
        }

        $webDir = \StringUtil::stripRootDir($container->getParameter('contao.web_dir'));

        // Create the file
        \File::putContent($webDir . '/share/' . $strFile . '.xml', $this->replaceInsertTags($objFeed->$strType(), false));
    }

    /**
     * Return the page object with loaded details for the given page ID
     *
     * @param  integer        $intPageId
     * @return PageModel|null
     */
    private function getPageWithDetails($intPageId)
    {
        if (!isset(self::$arrPageCache[$intPageId]))
        {
            self::$arrPageCache[$intPageId] = PageModel::findWithDetails($intPageId);
        }

        return self::$arrPageCache[$intPageId];
    }

    /**
     * Creates a sub request for the given URI.
     */
    private function createSubRequest(string $uri, Request $request = null): Request
    {
        $cookies = null !== $request ? $request->cookies->all() : array();
        $server = null !== $request ? $request->server->all() : array();

        unset($server['HTTP_IF_MODIFIED_SINCE'], $server['HTTP_IF_NONE_MATCH']);

        $subRequest = Request::create($uri, 'get', array(), $cookies, array(), $server);

        if (null !== $request)
        {
            if ($request->get('_format'))
            {
                $subRequest->attributes->set('_format', $request->get('_format'));
            }

            if ($request->getDefaultLocale() !== $request->getLocale())
            {
                $subRequest->setLocale($request->getLocale());
            }
        }

        // Always set a session (#3856)
        $subRequest->setSession(new Session(new MockArraySessionStorage()));

        return $subRequest;
    }
}
