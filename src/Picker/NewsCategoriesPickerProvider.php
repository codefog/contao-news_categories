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
        return ['fieldType' => 'checkbox'];
    }

    /**
     * @inheritDoc
     */
    public function convertDcaValue(PickerConfig $config, $value)
    {
        if (!$value) {
            return $value;
        }

        return is_array($value) ? array_map('intval', $value) : (int) $value;
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
        // @todo – check this
        return true;
    }
}
