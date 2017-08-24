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

        if (in_array($elements[0], $this->supportedTags, true)) {
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
        if (null === ($news = \NewsModel::findByIdOrAlias($idOrAlias))) {
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
        switch ($insertTag) {
            case 'news_category_link':

                if ($news->source !== 'default' || ($strUrl = $this->getNewsUrl($news)) === null) {
                    return \Controller::replaceInsertTags('{{news::'.$news->id.'}}', false);
                }

                return sprintf(
                    '<a href="%s" title="%s">%s</a>',
                    $this->getLink($news, $strUrl),
                    \StringUtil::specialchars($news->headline),
                    $news->headline
                );

            case 'news_category_link_open':

                if ($news->source !== 'default' || ($strUrl = $this->getNewsUrl($news)) === null) {
                    return \Controller::replaceInsertTags('{{news_open::'.$news->id.'}}', false);
                }

                return sprintf(
                    '<a href="%s" title="%s">',
                    $this->getLink($news, $strUrl),
                    \StringUtil::specialchars($news->headline)
                );

            case 'news_category_url':

                if ($news->source !== 'default' || ($strUrl = $this->getNewsUrl($news)) === null) {
                    return \Controller::replaceInsertTags('{{news_url::'.$news->id.'}}', false);
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