<?php

declare(strict_types=1);

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\EventListener\DataContainer;

use Codefog\NewsCategoriesBundle\Security\NewsCategoriesPermissions;
use Symfony\Bundle\SecurityBundle\Security;

class NewsArchiveListener
{
    public function __construct(private readonly Security $security)
    {
    }

    /**
     * On data container load.
     */
    public function onLoadCallback(): void
    {
        if (!$this->security->isGranted(NewsCategoriesPermissions::USER_CAN_MANAGE_CATEGORIES)) {
            unset($GLOBALS['TL_DCA']['tl_news_archive']['list']['global_operations']['categories']);
        }
    }
}
