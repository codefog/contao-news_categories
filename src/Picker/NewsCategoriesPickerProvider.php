<?php

declare(strict_types=1);

namespace Codefog\NewsCategoriesBundle\Picker;

use Contao\CoreBundle\DependencyInjection\Attribute\AsPickerProvider;
use Contao\CoreBundle\Picker\AbstractPickerProvider;
use Contao\CoreBundle\Picker\DcaPickerProviderInterface;
use Contao\CoreBundle\Picker\PickerConfig;

#[AsPickerProvider]
class NewsCategoriesPickerProvider extends AbstractPickerProvider implements DcaPickerProviderInterface
{
    public const CONTEXT = 'news_categories';

    public function getName(): string
    {
        return 'newsCategoriesPicker';
    }

    public function supportsContext(string $context): bool
    {
        return self::CONTEXT === $context;
    }

    public function supportsValue(PickerConfig $config): bool
    {
        return true;
    }

    public function getDcaTable(PickerConfig|null $config = null): string
    {
        return 'tl_news_category';
    }

    public function getDcaAttributes(PickerConfig $config): array
    {
        $attributes = ['fieldType' => 'radio'];

        if ($fieldType = $config->getExtra('fieldType')) {
            $attributes['fieldType'] = $fieldType;
        }

        if ($value = $config->getValue()) {
            $attributes['value'] = array_map(\intval(...), explode(',', $value));
        }

        return $attributes;
    }

    public function convertDcaValue(PickerConfig $config, mixed $value): int|string
    {
        return (int) $value;
    }

    protected function getRouteParameters(PickerConfig|null $config = null): array
    {
        return ['do' => 'news', 'table' => 'tl_news_category'];
    }
}
