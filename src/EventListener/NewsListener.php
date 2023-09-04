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
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\Model\Collection;
use Contao\ModuleNewsList;

class NewsListener implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    /**
     * InsertTagsListener constructor.
     */
    public function __construct(private readonly NewsCriteriaBuilder $searchBuilder)
    {
    }

    /**
     * On news list count items.
     *
     * @return int
     */
    public function onNewsListCountItems(array $archives, bool|null $featured, ModuleNewsList $module)
    {
        if (null === ($criteria = $this->getCriteria($archives, $featured, $module))) {
            return 0;
        }

        return $criteria->getNewsModelAdapter()->countBy($criteria->getColumns(), $criteria->getValues());
    }

    /**
     * On news list fetch items.
     *
     * @param int $limit
     * @param int $offset
     */
    public function onNewsListFetchItems(array $archives, bool|null $featured, $limit, $offset, ModuleNewsList $module): Collection|null
    {
        if (null === ($criteria = $this->getCriteria($archives, $featured, $module))) {
            return null;
        }

        $criteria->setLimit($limit);
        $criteria->setOffset($offset);

        return $criteria->getNewsModelAdapter()->findBy(
            $criteria->getColumns(),
            $criteria->getValues(),
            $criteria->getOptions(),
        );
    }

    /**
     * Get the criteria.
     *
     * @throws PageNotFoundException
     */
    private function getCriteria(array $archives, bool|null $featured, ModuleNewsList $module): NewsCriteria|null
    {
        try {
            $criteria = $this->searchBuilder->getCriteriaForListModule($archives, $featured, $module);
        } catch (CategoryNotFoundException $e) {
            throw new PageNotFoundException($e->getMessage());
        }

        return $criteria;
    }
}
