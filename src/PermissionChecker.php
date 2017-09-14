<?php

namespace Codefog\NewsCategoriesBundle;

use Contao\BackendUser;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;

class PermissionChecker
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * PermissionChecker constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Return true if the user can manage news categories
     *
     * @return bool
     */
    public function canUserManageNewsCategories()
    {
        $user = $this->getUser();

        if (!$user->isAdmin && !$user->hasAccess('manage', 'newscategories')) {
            return false;
        }

        return true;
    }

    /**
     * Return true if the user can assign news categories
     *
     * @return bool
     */
    public function canUserAssignNewsCategories()
    {
        $user = $this->getUser();

        return $user->isAdmin || in_array('tl_news::categories', $user->alexf, true);
    }

    /**
     * Get the user default categories
     *
     * @return array
     */
    public function getUserDefaultCategories()
    {
        $user = $this->getUser();

        return is_array($user->newscategories_default) ? $user->newscategories_default : [];
    }

    /**
     * Get the user
     *
     * @return BackendUser
     */
    private function getUser()
    {
        /** @var BackendUser $user */
        $user = $this->framework->createInstance(BackendUser::class);

        return $user;
    }
}
