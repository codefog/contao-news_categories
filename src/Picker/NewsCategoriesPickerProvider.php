<?php

declare(strict_types=1);

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\Picker;

use Codefog\NewsCategoriesBundle\Security\NewsCategoriesPermissions;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Picker\AbstractTablePickerProvider;
use Contao\CoreBundle\Picker\PickerConfig;
use Contao\DC_Table;
use Doctrine\DBAL\Connection;
use Knp\Menu\FactoryInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class NewsCategoriesPickerProvider extends AbstractTablePickerProvider
{
    public function __construct(
        ContaoFramework $framework,
        FactoryInterface $menuFactory,
        RouterInterface $router,
        TranslatorInterface $translator,
        Connection $connection,
        private readonly Security $security,
    ) {
        parent::__construct($framework, $menuFactory, $router, $translator, $connection);
    }

    public function getTableFromContext(string|null $context = null): string
    {
        return 'tl_news_category';
    }

    public function getDcaAttributes(PickerConfig $config): array
    {
        $attributes = parent::getDcaAttributes($config);

        if (\is_array($rootNodes = $config->getExtra('rootNodes'))) {
            $attributes['rootNodes'] = $rootNodes;
        }

        return $attributes;
    }

    public function getName(): string
    {
        return 'newsCategoriesPicker';
    }

    public function supportsContext(string $context): bool
    {
        return 'newsCategories' === $context && (
            $this->security->isGranted(NewsCategoriesPermissions::USER_CAN_MANAGE_CATEGORIES)
            || $this->security->isGranted(NewsCategoriesPermissions::USER_CAN_ASSIGN_CATEGORIES)
        );
    }

    protected function getDataContainer(): string
    {
        return DC_Table::class;
    }
}
