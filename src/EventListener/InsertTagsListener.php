<?php

namespace Codefog\NewsCategoriesBundle\EventListener;

use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Codefog\NewsCategoriesBundle\NewsCategoriesManager;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\Input;

class InsertTagsListener implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    /**
     * @var NewsCategoriesManager
     */
    private $manager;

    /**
     * InsertTagsListener constructor.
     *
     * @param NewsCategoriesManager $manager
     */
    public function __construct(NewsCategoriesManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * On replace the insert tags
     *
     * @param string $tag
     *
     * @return string|bool
     */
    public function onReplace($tag)
    {
        $chunks = trimsplit('::', $tag);

        if ($chunks[0] === 'news_categories') {
            /** @var Input $input */
            $input = $this->framework->getAdapter(Input::class);

            if ($alias = $input->get($this->manager->getParameterName())) {
                /** @var NewsCategoryModel $model */
                $model = $this->framework->getAdapter(NewsCategoryModel::class);

                if (($category = $model->findPublishedByIdOrAlias($alias)) !== null) {
                    return $category->{$chunks[1]};
                }
            }
        }

        return false;
    }
}
