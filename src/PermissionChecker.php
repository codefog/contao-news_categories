<?php

namespace Codefog\NewsCategoriesBundle;

use Contao\BackendUser;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PermissionChecker
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * PermissionChecker constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Return true if the user can manage news categories
     *
     * @return bool
     */
    public function canUserManageNewsCategories()
    {
        return $this->getUser()->hasAccess('manage', 'newscategories');
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
     *
     * @throws \RuntimeException
     */
    private function getUser()
    {
        if (null === $this->tokenStorage) {
            throw new \RuntimeException('No token storage provided');
        }

        $token = $this->tokenStorage->getToken();

        if (null === $token) {
            throw new \RuntimeException('No token provided');
        }

        $user = $token->getUser();

        if (!$user instanceof BackendUser) {
            throw new \RuntimeException('The token does not contain a back end user object');
        }

        return $user;
    }
}
