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
     * @var NewsCriteriaBuilder
     */
    private $searchBuilder;

    /**
     * InsertTagsListener constructor.
     */
    public function __construct(NewsCriteriaBuilder $searchBuilder)
    {
        $this->searchBuilder = $searchBuilder;
    }

    /**
     * On news list count items.
     *
     * @param bool|null $featured
     *
     * @return int
     */
    public function onNewsListCountItems(array $archives, $featured, ModuleNewsList $module)
    {
        if (null === ($criteria = $this->getCriteria($archives, $featured, $module))) {
            return 0;
        }

        return $criteria->getNewsModelAdapter()->countBy($criteria->getColumns(), $criteria->getValues());
    }

    /**
     * On news list fetch items.
     *
     * @param bool|null $featured
     * @param int       $limit
     * @param int       $offset
     *
     * @return Collection|null
     */
    public function onNewsListFetchItems(array $archives, $featured, $limit, $offset, ModuleNewsList $module)
    {
        if (null === ($criteria = $this->getCriteria($archives, $featured, $module))) {
            return null;
        }

        $criteria->setLimit($limit);
        $criteria->setOffset($offset);

        return $criteria->getNewsModelAdapter()->findBy(
            $criteria->getColumns(),
            $criteria->getValues(),
            $criteria->getOptions()
        );
    }

    /**
     * Get the criteria.
     *
     * @param bool|null $featured
     *
     * @return NewsCriteria|null
     *
     * @throws PageNotFoundException
     */
    private function getCriteria(array $archives, $featured, ModuleNewsList $module)
    {
        try {
            $criteria = $this->searchBuilder->getCriteriaForListModule($archives, $featured, $module);
        } catch (CategoryNotFoundException $e) {
            throw new PageNotFoundException($e->getMessage());
        }

        return $criteria;
    }
}
