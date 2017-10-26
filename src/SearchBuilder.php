<?php

namespace Codefog\NewsCategoriesBundle;

use Codefog\NewsCategoriesBundle\Exception\NoNewsException;
use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\Module;
use Contao\StringUtil;
use Symfony\Component\HttpFoundation\Request;

class SearchBuilder implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    /**
     * Get the criteria for module
     *
     * @param array     $archives
     * @param bool|null $featured
     * @param Module    $module
     * @param Request   $request
     *
     * @return Criteria|null
     */
    public function getCriteriaForModule(array $archives, $featured, Module $module, Request $request)
    {
        $criteria = new Criteria($this->framework);

        // DO NOT CHANGE THE ORDER OF CRITERIA METHODS!!!
        try {
            $criteria->setBasicCriteria($archives, $featured);

            // Generate only related categories
            if ($module->news_relatedCategories) {
                // @todo
            }

            // Filter by default categories
            if (count($default = StringUtil::deserialize($module->news_filterDefault, true)) > 0) {
                $criteria->setDefaultCategories($default);
            }

            // Filter by active category
            if ($module->news_filterCategories) {
                // @todo – change this
                $param = \NewsCategories\NewsCategories::getParameterName();

                if ($request->query->has($param)) {
                    $category = NewsCategoryModel::findPublishedByIdOrAlias($request->query->get($param));

                    // Return null if the category does not exist
                    if ($category === null) {
                        return null;
                    }

                    $criteria->setCategory($category->id, (bool) $module->news_filterPreserve);
                }
            }
        } catch (NoNewsException $e) {
            return null;
        }

        return $criteria;
    }
}
