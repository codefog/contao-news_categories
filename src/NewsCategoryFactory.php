<?php

namespace Codefog\NewsCategoriesBundle;

use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;

class NewsCategoryFactory implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    /**
     * Create a single news category instance
     *
     * @param int $id
     *
     * @return NewsCategory
     *
     * @throws \InvalidArgumentException
     */
    public function create($id)
    {
        if (($model = $this->getModelAdapter()->findByPk($id)) === null) {
            throw new \InvalidArgumentException(sprintf('News category ID %s does not exist', $id));
        }

        return new NewsCategory($model);
    }

    /**
     * Create multiple news category instances
     *
     * @param array $ids
     *
     * @return array
     */
    public function createMultiple(array $ids)
    {
        $categories = [];

        if (($models = $this->getModelAdapter()->findMultipleByIds($ids)) !== null) {
            /** @var NewsCategoryModel $model */
            foreach ($models as $model) {
                $categories[$model->id] = new NewsCategory($model);
            }
        }

        return $categories;
    }

    /**
     * Get the model adapter
     *
     * @return NewsCategoryModel
     */
    private function getModelAdapter()
    {
        /** @var NewsCategoryModel $adapter */
        $adapter = $this->framework->getAdapter(NewsCategoryModel::class);

        return $adapter;
    }
}
