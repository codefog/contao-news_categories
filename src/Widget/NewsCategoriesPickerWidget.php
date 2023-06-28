<?php

/*
 * News Categories Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\Widget;

use Contao\Picker;

/**
 * @property array $rootNodes
 */
class NewsCategoriesPickerWidget extends Picker
{
    public function generate(): string
    {
        $this->context = 'newsCategories';

        return parent::generate();
    }

    protected function getRelatedTable(): string
    {
        return 'tl_news_category';
    }

    protected function getPickerUrlExtras($values = []): array
    {
        $extras = parent::getPickerUrlExtras($values);

        if (\is_array($this->rootNodes))
        {
            $extras['rootNodes'] = array_values($this->rootNodes);
        }

        return $extras;
    }
}
