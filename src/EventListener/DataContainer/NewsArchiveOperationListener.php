<?php

declare(strict_types=1);

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2023, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\EventListener\DataContainer;

use Codefog\NewsCategoriesBundle\Security\NewsCategoriesPermissions;
use Contao\ArrayUtil;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Symfony\Bundle\SecurityBundle\Security;

#[AsCallback('tl_news_archive', 'config.onload')]
class NewsArchiveOperationListener
{
    public function __construct(private readonly Security $security)
    {
    }

    public function __invoke(): void
    {
        if (!$this->security->isGranted(NewsCategoriesPermissions::USER_CAN_MANAGE_CATEGORIES)) {
            return;
        }

        ArrayUtil::arrayInsert(
            $GLOBALS['TL_DCA']['tl_news_archive']['list']['global_operations'], 1, [
                'categories' => [
                    'label' => &$GLOBALS['TL_LANG']['tl_news_archive']['categories'],
                    'href' => 'table=tl_news_category',
                    'icon' => 'bundles/codefognewscategories/icon.png',
                    'attributes' => 'onclick="Backend.getScrollOffset()"',
                ],
            ],
        );
    }
}
