<?php

declare(strict_types=1);

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2023, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\FrontendModule;

use Contao\Input;
use Contao\ModuleNewsList;
use Contao\NewsModel;
use Contao\StringUtil;
use Contao\System;

/**
 * @property string|array $news_relatedCategories
 */
class NewsListModule extends ModuleNewsList
{
    /**
     * Current news for future reference in search builder.
     */
    public NewsModel|null $currentNews = null;

    /**
     * Set the flag to filter news by categories.
     *
     * @return string
     */
    public function generate()
    {
        $container = System::getContainer();

        if (
            ($request = $container->get('request_stack')->getCurrentRequest())
            && $container->get('contao.routing.scope_matcher')->isBackendRequest($request)
        ) {
            return parent::generate();
        }

        // Generate the list in related categories mode
        if ($this->news_relatedCategories) {
            return $this->generateRelated();
        }

        return parent::generate();
    }

    /**
     * Generate the list in related categories mode.
     *
     * Use the categories of the current news item. The module must be
     * on the same page as news reader module.
     *
     * @return string
     */
    protected function generateRelated()
    {
        $this->news_archives = $this->sortOutProtected(StringUtil::deserialize($this->news_archives, true));

        // Return if there are no archives
        if (0 === \count($this->news_archives)) {
            return '';
        }

        $alias = Input::get('items') ?: Input::get('auto_item');

        // Return if the news item was not found
        if (($news = NewsModel::findPublishedByParentAndIdOrAlias($alias, $this->news_archives)) === null) {
            return '';
        }

        // Store the news item for further reference
        $this->currentNews = $news;

        return parent::generate();
    }
}
