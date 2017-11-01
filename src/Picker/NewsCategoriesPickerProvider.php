<?php

namespace Codefog\NewsCategoriesBundle\Picker;

use Contao\CoreBundle\Picker\AbstractPickerProvider;
use Contao\CoreBundle\Picker\DcaPickerProviderInterface;
use Contao\CoreBundle\Picker\PickerConfig;

class NewsCategoriesPickerProvider extends AbstractPickerProvider implements DcaPickerProviderInterface
{
    /**
     * @inheritDoc
     */
    protected function getRouteParameters(PickerConfig $config = null)
    {
        return ['do' => 'news', 'table' => 'tl_news_category'];
    }

    /**
     * @inheritDoc
     */
    public function getDcaTable()
    {
        return 'tl_news_category';
    }

    /**
     * @inheritDoc
     */
    public function getDcaAttributes(PickerConfig $config)
    {
        $attributes = ['fieldType' => 'checkbox'];

        if ($this->supportsValue($config)) {
            $attributes['value'] = array_map('intval', explode(',', $config->getValue()));
        }

        return $attributes;
    }

    /**
     * @inheritDoc
     */
    public function convertDcaValue(PickerConfig $config, $value)
    {
        return (int) $value;
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'newsCategoriesPicker';
    }

    /**
     * @inheritDoc
     */
    public function supportsContext($context)
    {
        return 'newsCategories' === $context && $this->getUser()->hasAccess('manage', 'newscategories');
    }

    /**
     * @inheritDoc
     */
    public function supportsValue(PickerConfig $config)
    {
        foreach (explode(',', $config->getValue()) as $id) {
            if (!is_numeric($id)) {
                return false;
            }
        }

        return true;
    }
}
