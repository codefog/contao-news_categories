<?php

namespace Codefog\NewsCategoriesBundle\EventListener;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\Input;

class InsertTagsListener implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

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

            if ($alias = $input->get(\NewsCategories\NewsCategories::getParameterName())) {
                $className = \NewsCategories\NewsCategories::getModelClass();

                if (($category = $className::findPublishedByIdOrAlias($input->get($alias))) !== null) {
                    return $category->{$chunks[1]};
                }
            }
        }

        return false;
    }
}
