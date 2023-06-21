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

use Codefog\NewsCategoriesBundle\PermissionChecker;

class NewsArchiveListener
{
    /**
     * NewsArchiveListener constructor.
     */
    public function __construct(private readonly PermissionChecker $permissionChecker)
    {
    }

    /**
     * On data container load.
     */
    public function onLoadCallback(): void
    {
        if (!$this->permissionChecker->canUserManageCategories()) {
            unset($GLOBALS['TL_DCA']['tl_news_archive']['list']['global_operations']['categories']);
        }
    }
}
