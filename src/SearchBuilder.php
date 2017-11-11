<?php

namespace Codefog\NewsCategoriesBundle;

use Codefog\NewsCategoriesBundle\Exception\NoNewsException;
use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\Module;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Haste\Model\Model;
use Symfony\Component\HttpFoundation\Request;

class SearchBuilder implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    /**
     * @var Connection
     */
    private $db;

    /**
     * @var UrlGenerator
     */
    private $urlGenerator;

    /**
     * SearchBuilder constructor.
     *
     * @param Connection   $db
     * @param UrlGenerator $urlGenerator
     */
    public function __construct(Connection $db, UrlGenerator $urlGenerator)
    {
        $this->db = $db;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Get the criteria for archive module
     *
     * @param array   $archives
     * @param int     $begin
     * @param int     $end
     * @param Module  $module
     * @param Request $request
     *
     * @return Criteria|null
     */
    public function getCriteriaForArchiveModule(array $archives, $begin, $end, Module $module, Request $request)
    {
        $criteria = new Criteria($this->framework);

        try {
            $criteria->setBasicCriteria($archives);

            // Set the time frame
            $criteria->setTimeFrame($begin, $end);

            // Set the regular list criteria
            $this->setRegularListCriteria($criteria, $module, $request);
        } catch (NoNewsException $e) {
            return null;
        }

        return $criteria;
    }

    /**
     * Get the criteria for list module
     *
     * @param array     $archives
     * @param bool|null $featured
     * @param Module    $module
     * @param Request   $request
     *
     * @return Criteria|null
     */
    public function getCriteriaForListModule(array $archives, $featured, Module $module, Request $request)
    {
        $criteria = new Criteria($this->framework);

        try {
            $criteria->setBasicCriteria($archives);

            // Set the featured filter
            if ($featured !== null) {
                $criteria->setFeatured($featured);
            }

            // Set the criteria for related categories
            if ($module->news_relatedCategories) {
                $this->setRelatedListCriteria($criteria, $module);
            } else {
                // Set the regular list criteria
                $this->setRegularListCriteria($criteria, $module, $request);
            }
        } catch (NoNewsException $e) {
            return null;
        }

        return $criteria;
    }

    /**
     * Set the regular list criteria
     *
     * @param Criteria $criteria
     * @param Module   $module
     * @param Request  $request
     *
     * @throws NoNewsException
     */
    private function setRegularListCriteria(Criteria $criteria, Module $module, Request $request)
    {
        // Filter by default categories
        if (count($default = StringUtil::deserialize($module->news_filterDefault, true)) > 0) {
            $criteria->setDefaultCategories($default);
        }

        // Filter by active category
        if ($module->news_filterCategories) {
            $param = $this->urlGenerator->getParameterName();

            if ($request->query->has($param)) {
                /** @var NewsCategoryModel $model */
                $model = $this->framework->getAdapter(NewsCategoryModel::class);

                $category = $model->findPublishedByIdOrAlias($request->query->get($param));

                // Return null if the category does not exist
                if ($category === null) {
                    throw new NoNewsException();
                }

                $criteria->setCategory($category->id, (bool) $module->news_filterPreserve);
            }
        }
    }

    /**
     * Set the related list criteria
     *
     * @param Criteria $criteria
     * @param Module   $module
     *
     * @throws NoNewsException
     */
    private function setRelatedListCriteria(Criteria $criteria, Module $module)
    {
        if (($news = $module->currentNews) === null) {
            throw new NoNewsException();
        }

        /** @var Model $adapter */
        $adapter = $this->framework->getAdapter(Model::class);
        $categories = array_unique($adapter->getReferenceValues($news->getTable(), 'categories', $news->id));

        // This news has no news categories assigned
        if (count($categories) === 0) {
            throw new NoNewsException();
        }

        $categories = array_map('intval', $categories);
        $excluded = $this->db->fetchAll('SELECT id FROM tl_news_category WHERE excludeInRelated=1');

        // Exclude the categories
        foreach ($excluded as $category) {
            if (($index = array_search((int) $category['id'], $categories, true)) !== false) {
                unset($categories[$index]);
            }
        }

        // There are no categories left
        if (count($categories) === 0) {
            throw new NoNewsException();
        }

        $criteria->setDefaultCategories($categories);
        $criteria->setExcludedNews([$news->id]);
    }
}
