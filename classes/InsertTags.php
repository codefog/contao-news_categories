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
     * @var array
     */
    private $supportedTags = [
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

        if (in_array($elements[0], $this->supportedTags, true))
        {
            return $this->replaceNewsInsertTags($elements[0], $elements[1]);
        }

        return false;

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
        if (null === ($news = \NewsModel::findByIdOrAlias($idOrAlias)))
        {
            return '';
        }

        return $this->generateReplacement($news, $insertTag);
    }

    /**
     * Generates the replacement string.
     *
     * @param \NewsModel $news
     * @param string     $insertTag
     *
     * @return string
     */
    private function generateReplacement($news, $insertTag)
    {
        switch ($insertTag)
        {
            case 'news_category_link':

                if (($strUrl = $this->getNewsUrl($news)) === null)
                {
                    return '';
                }

                return sprintf(
                    '<a href="%s" title="%s">%s</a>',
                    $this->getLink($news, $strUrl),
                    \StringUtil::specialchars($news->headline),
                    $news->headline
                );

            case 'news_category_link_open':

                if (($strUrl = $this->getNewsUrl($news)) === null)
                {
                    return '';
                }

                return sprintf(
                    '<a href="%s" title="%s">',
                    $this->getLink($news, $strUrl),
                    \StringUtil::specialchars($news->headline)
                );

            case 'news_category_url':

                if (($strUrl = $this->getNewsUrl($news)) === null)
                {
                    return '';
                }

                return $this->getLink($news, $strUrl);
        }

        return '';
    }

    /**
     * Get the news jumpTo url
     *
     * @param $news
     *
     * @return string|null
     */
    private function getNewsUrl($news)
    {
        /** @var \PageModel $objPage */
        $objPage = $news->getRelated('pid');

        $jumpTo = $objPage->jumpTo;

        // No jumpTo page set (see #4784)
        if (!$jumpTo)
        {
            return null;
        }

        $objParent = \PageModel::findWithDetails($jumpTo);

        // A jumpTo page is set but does no longer exist (see #5781)
        if ($objParent === null)
        {
            $strUrl = false;
        }
        else
        {
            $strUrl = $objParent->getAbsoluteUrl((\Config::get('useAutoItem') && !\Config::get('disableAlias')) ? '/%s' : '/items/%s');
        }

        // Skip the event if it requires a jumpTo URL but there is none
        if ($strUrl === false && $news->source == 'default')
        {
            return null;
        }

        return $strUrl;
    }
}