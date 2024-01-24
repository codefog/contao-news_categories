<?php

declare(strict_types=1);

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\EventListener;

use Codefog\NewsCategoriesBundle\Criteria\NewsCriteria;
use Codefog\NewsCategoriesBundle\Criteria\NewsCriteriaBuilder;
use Codefog\NewsCategoriesBundle\Exception\CategoryNotFoundException;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\Model\Collection;
use Contao\ModuleNewsList;
use Contao\NewsModel;

class NewsListener
{
    public function __construct(private readonly NewsCriteriaBuilder $searchBuilder)
    {
    }

    #[AsHook('newsListCountItems')]
    public function onNewsListCountItems(array $archives, bool|null $featured, ModuleNewsList $module): int
    {
        if (null === ($criteria = $this->getCriteria($archives, $featured, $module))) {
            return 0;
        }

        return NewsModel::countBy($criteria->getColumns(), $criteria->getValues());
    }

    /**
     * @return Collection<NewsModel>|null
     */
    #[AsHook('onNewsListFetchItems')]
    public function onNewsListFetchItems(array $archives, bool|null $featured, int $limit, int $offset, ModuleNewsList $module): Collection|null
    {
        if (null === ($criteria = $this->getCriteria($archives, $featured, $module))) {
            return null;
        }

        $criteria->setLimit($limit);
        $criteria->setOffset($offset);

        return NewsModel::findBy(
            $criteria->getColumns(),
            $criteria->getValues(),
            $criteria->getOptions(),
        );
    }

    private function getCriteria(array $archives, bool|null $featured, ModuleNewsList $module): NewsCriteria|null
    {
        try {
            $criteria = $this->searchBuilder->getCriteriaForListModule($archives, $featured, $module);
        } catch (CategoryNotFoundException $e) {
            throw new PageNotFoundException($e->getMessage(), 0, $e);
        }

        return $criteria;
    }
}
