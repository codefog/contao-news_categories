<?php

/*
 * News Categories Bundle for Contao Open Source CMS.
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
     * @var PermissionChecker
     */
    private $permissionChecker;

    /**
     * NewsArchiveListener constructor.
     *
     * @param PermissionChecker $permissionChecker
     */
    public function __construct(PermissionChecker $permissionChecker)
    {
        $this->permissionChecker = $permissionChecker;
    }

    /**
     * On data container load.
     */
    public function onLoadCallback()
    {
        if (!$this->permissionChecker->canUserManageCategories()) {
            unset($GLOBALS['TL_DCA']['tl_news_archive']['list']['global_operations']['limitCategories']);
        }
    }
}
