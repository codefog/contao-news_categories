<?php

namespace Codefog\NewsCategoriesBundle;

use Contao\BackendUser;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\Database;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PermissionChecker implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    /**
     * @var Connection
     */
    private $db;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * PermissionChecker constructor.
     *
     * @param Connection            $db
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(Connection $db, TokenStorageInterface $tokenStorage)
    {
        $this->db = $db;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Return true if the user can manage news categories
     *
     * @return bool
     */
    public function canUserManageCategories()
    {
        return $this->getUser()->hasAccess('manage', 'newscategories');
    }

    /**
     * Return true if the user can assign news categories
     *
     * @return bool
     */
    public function canUserAssignCategories()
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
     * Get the user allowed roots. Return null if the user has no limitation
     *
     * @return array|null
     */
    public function getUserAllowedRoots()
    {
        $user = $this->getUser();

        if ($user->isAdmin) {
            return null;
        }

        return array_map('intval', (array) $user->newscategories_roots);
    }

    /**
     * Return if the user is allowed to manage the news category
     *
     * @param int $categoryId
     *
     * @return bool
     */
    public function isUserAllowedNewsCategory($categoryId)
    {
        if (($roots = $this->getUserAllowedRoots()) === null) {
            return true;
        }

        /** @var Database $db */
        $db = $this->framework->createInstance(Database::class);

        $ids = $db->getChildRecords($roots, 'tl_news_category', false, $roots);
        $ids = array_map('intval', $ids);

        return in_array((int) $categoryId, $ids, true);
    }

    /**
     * Add the category to allowed roots
     *
     * @param int $categoryId
     */
    public function addCategoryToAllowedRoots($categoryId)
    {
        if (($roots = $this->getUserAllowedRoots()) === null) {
            return;
        }

        $categoryId = (int) $categoryId;
        $user = $this->getUser();

        /** @var StringUtil $stringUtil */
        $stringUtil = $this->framework->getAdapter(StringUtil::class);

        // Add the permissions on group level
        if ($user->inherit !== 'custom') {
            $groups = $this->db->fetchAll('SELECT id, newscategories, newscategories_roots FROM tl_user_group WHERE id IN(' . implode(',', array_map('intval', $user->groups)) . ')');

            foreach ($groups as $group) {
                $permissions = $stringUtil->deserialize($group['newscategories'], true);

                if (in_array('manage', $permissions, true)) {
                    $categoryIds = $stringUtil->deserialize($group['newscategories_roots'], true);
                    $categoryIds[] = $categoryId;

                    $this->db->update('tl_user_group', ['newscategories_roots' => serialize($categoryIds)], ['id' => $group['id']]);
                }
            }
        }

        // Add the permissions on user level
        if ($user->inherit !== 'group') {
            $userData = $this->db->fetchAssoc('SELECT newscategories, newscategories_roots FROM tl_user WHERE id=?', [$user->id]);
            $permissions = $stringUtil->deserialize($userData['newscategories'], true);

            if (in_array('manage', $permissions, true)) {
                $categoryIds = $stringUtil->deserialize($userData['newscategories_roots'], true);
                $categoryIds[] = $categoryId;

                $this->db->update('tl_user', ['newscategories_roots' => serialize($categoryIds)], ['id' => $user->id]);
            }
        }

        // Add the new element to the user object
        $user->newscategories_roots = array_merge($roots, [$categoryId]);
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
