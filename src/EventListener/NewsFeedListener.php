<?php

declare(strict_types=1);

/*
 * News Categories Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\EventListener;

use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Contao\NewsBundle\Event\FetchArticlesForFeedEvent;
use Contao\NewsBundle\Event\TransformArticleForFeedEvent;
use Contao\NewsModel;
use Contao\StringUtil;
use Symfony\Contracts\Translation\TranslatorInterface;

class NewsFeedListener
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function onFetchArticlesForFeed(FetchArticlesForFeedEvent $event): void
    {
        $ids = StringUtil::deserialize($event->getPageModel()->newsCategories, true);

        if (empty($ids)) {
            return;
        }

        // Articles from the Contao news bundle
        $articles = $event->getArticles();

        /** @var NewsModel $article */
        foreach ($articles as $k => $article) {
            $categories = array_map(
                static fn (NewsCategoryModel $category) => $category->id,
                NewsCategoryModel::findPublishedByNews($article->id, ['return' => 'Array'])
            );

            if (!array_intersect($ids, $categories)) {
                unset($articles[$k]);
            }
        }

        $event->setArticles(array_values($articles));
    }

    public function onTransformArticleForFeed(TransformArticleForFeedEvent $event): void
    {
        $page = $event->getPageModel();
        $feedItem = $event->getItem();

        if (
            !$feedItem
            || !$page->newsCategories_show
            || null === ($categoryModels = NewsCategoryModel::findPublishedByNews($event->getArticle()->id))
        ) {
            return;
        }

        $categories = implode(', ', array_map(static fn (NewsCategoryModel $category) => $category->getTitle(), $categoryModels));

        switch ($page->newsCategories_show) {
            case 'title':
                $feedItem->setTitle(sprintf('[%s] %s', $categories, $feedItem->getTitle()));
                break;

            case 'text_before':
                $feedItem->setContent(sprintf(
                    "%s\n<p>%s %s</p>",
                    (string) $feedItem->getContent(),
                    $this->translator->trans('MSC.newsCategories', [], 'contao_default'),
                    $categories,
                ));
                break;

            case 'text_after':
                $feedItem->setContent(sprintf(
                    "<p>%s %s</p>\n%s",
                    $this->translator->trans('MSC.newsCategories', [], 'contao_default'),
                    $categories,
                    (string) $feedItem->getContent()
                ));
                break;
        }
    }
}
