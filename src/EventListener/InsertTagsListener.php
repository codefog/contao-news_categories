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
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Input;
use Contao\StringUtil;

#[AsHook('replaceInsertTags')]
class InsertTagsListener
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly NewsCategoriesManager $manager,
    ) {
    }

    public function __invoke(string $tag): string|false
    {
        $chunks = StringUtil::trimsplit('::', $tag);

        if ('news_categories' === $chunks[0]) {
            $input = $this->framework->getAdapter(Input::class);

            if ($alias = $input->get($this->manager->getParameterName())) {
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
