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

use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Codefog\NewsCategoriesBundle\NewsCategoriesManager;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\Input;
use Contao\StringUtil;

class InsertTagsListener implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    /**
     * @var NewsCategoriesManager
     */
    private $manager;

    /**
     * InsertTagsListener constructor.
     */
    public function __construct(NewsCategoriesManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * On replace the insert tags.
     *
     * @param string $tag
     *
     * @return string|bool
     */
    public function onReplace($tag)
    {
        $chunks = StringUtil::trimsplit('::', $tag);

        if ('news_categories' === $chunks[0]) {
            /** @var Input $input */
            $input = $this->framework->getAdapter(Input::class);

            if ($alias = $input->get($this->manager->getParameterName())) {
                /** @var NewsCategoryModel $model */
                $model = $this->framework->getAdapter(NewsCategoryModel::class);

                if (null !== ($category = $model->findPublishedByIdOrAlias($alias))) {
                    $value = $category->{$chunks[1]};

                    // Convert the binary to UUID for images (#147)
                    if ('image' === $chunks[1] && $value) {
                        return StringUtil::binToUuid($value);
                    }

                    return $value;
                }
            }

            return '';
        }

        return false;
    }
}
