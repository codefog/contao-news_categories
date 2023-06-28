<?php

declare(strict_types=1);

namespace Codefog\NewsCategoriesBundle\Security;

final class NewsCategoriesPermissions
{
    public const USER_CAN_MANAGE_CATEGORIES = 'contao_user.newscategories.manage';

    public const USER_CAN_ASSIGN_CATEGORIES = 'contao_user.alexf.tl_news::categories';

    public const USER_CAN_ACCESS_CATEGORY = 'contao_user.newscategories_roots';
}
