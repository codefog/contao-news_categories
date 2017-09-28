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

class InsertTags extends News
{
    /**
     * Supported tags with category and news archive relation
     * @var array
     */
    private $supportedNewsArchiveCategoryTags = [
        'news_archive_category_page',
        'news_archive_category_page_url',
        'news_archive_category_page_title',
        'news_archive_category_page_link',
        'news_archive_category_page_name',
        'news_archive_category_page_link_open',
        'news_archive_category_teaser',
    ];

    /**
     * Supported tags with category relation
     * @var array
     */
    private $supportedCategoryTags = [
        'news_category_page',
        'news_category_alias',
        'news_category_teaser',
    ];

    /**
     * Supported tags with news relation
     * @var array
     */
    private $supportedNewsTags = [
        'news_category_link',
        'news_category_url',
        'news_category_link_open',
    ];

    /**
     * Add additional tags
     *
     * @param $strTag
     * @param $blnCache
     * @param $strCache
     * @param $flags
     * @param $tags
     * @param $arrCache
     * @param $index
     * @param $count
     *
     * @return mixed Return false, if the tag was not replaced, otherwise return the value of the replaced tag
     */
    public function replace($strTag, $blnCache, $strCache, $flags, $tags, $arrCache, $index, $count)
    {
        $elements = explode('::', $strTag);

        if (in_array($elements[0], $this->supportedNewsArchiveCategoryTags, true)) {
            return $this->replaceNewsArchiveCategoryInsertTags($elements[0], $elements[1], $elements[2]);
        }

        if (in_array($elements[0], $this->supportedCategoryTags, true)) {
            return $this->replaceCategoryInsertTags($elements[0], $elements[1]);
        }

        if (in_array($elements[0], $this->supportedNewsTags, true)) {
            return $this->replaceNewsInsertTags($elements[0], $elements[1]);
        }

        return false;
    }

    /**
     * Replaces a category and news archive related insert tag.
     *
     * @param string $insertTag
     * @param string $categoryIsOrAlias
     * @param int $newsArchive
     *
     * @return string
     */
    private function replaceNewsArchiveCategoryInsertTags($insertTag, $categoryIsOrAlias, $newsArchive)
    {
        if (null === ($category = NewsCategoryModel::findPublishedByIdOrAlias($categoryIsOrAlias))) {
            return '';
        }

        if (null === ($newsArchive = \NewsArchiveModel::findByPk($newsArchive))) {
            return '';
        }

        return $this->generateNewsArchiveCategoryReplacement($category, $newsArchive, $insertTag);
    }

    /**
     * Replaces a category-related insert tag.
     *
     * @param string $insertTag
     * @param string $idOrAlias
     *
     * @return string
     */
    private function replaceCategoryInsertTags($insertTag, $idOrAlias)
    {
        if (null === ($category = NewsCategoryModel::findPublishedByIdOrAlias($idOrAlias))) {
            return '';
        }

        return $this->generateCategoryReplacement($category, $insertTag);
    }

    /**
     * Replaces a news-related insert tag.
     *
     * @param string $insertTag
     * @param string $idOrAlias
     *
     * @return string
     */
    private function replaceNewsInsertTags($insertTag, $idOrAlias)
    {
        if (null === ($news = \NewsModel::findByIdOrAlias($idOrAlias))) {
            return '';
        }

        return $this->generateNewsReplacement($news, $insertTag);
    }

    /**
     * Generates the news related replacement string.
     *
     * @param \Contao\NewsModel $news
     * @param string $insertTag
     *
     * @return string
     */
    private function generateNewsReplacement($news, $insertTag)
    {
        switch ($insertTag) {
            case 'news_category_link':

                if ($news->source !== 'default' || ($strUrl = $this->getNewsUrl($news)) === null) {
                    return \Controller::replaceInsertTags('{{news::' . $news->id . '}}', false);
                }

                return sprintf(
                    '<a href="%s" title="%s">%s</a>',
                    $this->getLink($news, $strUrl),
                    \StringUtil::specialchars($news->headline),
                    $news->headline
                );

            case 'news_category_link_open':

                if ($news->source !== 'default' || ($strUrl = $this->getNewsUrl($news)) === null) {
                    return \Controller::replaceInsertTags('{{news_open::' . $news->id . '}}', false);
                }

                return sprintf(
                    '<a href="%s" title="%s">',
                    $this->getLink($news, $strUrl),
                    \StringUtil::specialchars($news->headline)
                );

            case 'news_category_url':

                if ($news->source !== 'default' || ($strUrl = $this->getNewsUrl($news)) === null) {
                    return \Controller::replaceInsertTags('{{news_url::' . $news->id . '}}', false);
                }

                return $this->getLink($news, $strUrl);
            case 'news_category_page':
                break;
        }

        return '';
    }

    /**
     * Generates the category related replacement string.
     *
     * @param NewsCategoryModel $category
     * @param string $insertTag
     *
     * @return string
     */
    private function generateCategoryReplacement($category, $insertTag)
    {
        switch ($insertTag) {
            case 'news_category_page':
                return $category->jumpTo ?: '';
            case 'news_category_alias':
                return $category->alias ?: '';
            case 'news_category_teaser':
                return $category->teaser ? \StringUtil::encodeEmail(\StringUtil::toHtml5($category->teaser)) : '';
        }

        return '';
    }

    /**
     * Generates the category and news archive related replacement string.
     *
     * @param NewsCategoryModel $category
     * @param \Contao\NewsArchiveModel $newsArchive
     * @param string $insertTag
     *
     * @return string
     */
    private function generateNewsArchiveCategoryReplacement($category, $newsArchive, $insertTag)
    {
        switch ($insertTag) {
            case 'news_archive_category_page':
                $pageId = $category->jumpTo;

                if (($page = CategoryHelper::getNewsArchiveCategoryPage($category->id, $newsArchive->id)) !== null) {
                    $pageId = $page->id;
                }

                return $pageId > 0 ? $pageId : '';

            case 'news_archive_category_page_url':
                if (($page = CategoryHelper::getNewsArchiveCategoryPage($category->id, $newsArchive->id)) === null) {
                    return '';
                }

                return \Controller::replaceInsertTags('{{link_url::' . $page->id . '}}');
            case 'news_archive_category_page_title':
                if (($page = CategoryHelper::getNewsArchiveCategoryPage($category->id, $newsArchive->id)) === null) {
                    return '';
                }

                return \Controller::replaceInsertTags('{{link_title::' . $page->id . '}}');
            case 'news_archive_category_page_link':
                if (($page = CategoryHelper::getNewsArchiveCategoryPage($category->id, $newsArchive->id)) === null) {
                    return '';
                }

                return \Controller::replaceInsertTags('{{link::' . $page->id . '}}');
            case 'news_archive_category_page_name':
                if (($page = CategoryHelper::getNewsArchiveCategoryPage($category->id, $newsArchive->id)) === null) {
                    return '';
                }

                return \Controller::replaceInsertTags('{{link_name::' . $page->id . '}}');
            case 'news_archive_category_page_link_open':
                if (($page = CategoryHelper::getNewsArchiveCategoryPage($category->id, $newsArchive->id)) === null) {
                    return '';
                }

                return \Controller::replaceInsertTags('{{link_open::' . $page->id . '}}');
            case 'news_archive_category_teaser':
                if (($config = CategoryHelper::getNewsArchiveCategoryConfig($category->id, $newsArchive->id)) === null) {
                    return '';
                }

                if ($config->news_category_teaser == '') {
                    return '';
                }

                return \StringUtil::encodeEmail(\StringUtil::toHtml5($config->news_category_teaser));
        }

        return '';
    }


    /**
     * Get the news jumpTo url
     *
     * @param \Contao\NewsModel $news
     *
     * @return string|null
     */
    private function getNewsUrl($news)
    {
        /** @var \NewsArchiveModel $objPage */
        $archive = $news->getRelated('pid');

        $jumpTo = $archive->jumpTo;

        // No jumpTo page set
        if (!$jumpTo) {
            return null;
        }

        // A jumpTo page is set but does no longer exist
        if (($objParent = \PageModel::findWithDetails($jumpTo)) === null) {
            $strUrl = false;
        } else {
            $strUrl = ampersand($objParent->getFrontendUrl((\Config::get('useAutoItem') && !\Config::get('disableAlias')) ? '/%s' : '/items/%s'));
        }

        // return if it requires a jumpTo URL but there is none
        if ($strUrl === false) {
            return null;
        }

        return $strUrl;
    }
}