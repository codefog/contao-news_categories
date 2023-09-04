<?php

declare(strict_types=1);

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle;

use Codefog\NewsCategoriesBundle\Security\NewsCategoriesPermissions;
use Contao\BackendUser;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\SecurityBundle\Security;

class PermissionChecker implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    public function __construct(
        private readonly Connection $db,
        private readonly Security $security,
    ) {
    }

    /**
     * Return true if the user can manage news categories.
     *
     * @return bool
     */
    public function canUserManageCategories()
    {
        trigger_deprecation('codefog/contao-news_categories', '3.5', __METHOD__.' has been deprecated, use $security->isGranted(NewsCategoriesPermissions::USER_CAN_MANAGE_CATEGORIES) instead.');

        return $this->security->isGranted(NewsCategoriesPermissions::USER_CAN_MANAGE_CATEGORIES);
    }

    /**
     * Return true if the user can assign news categories.
     *
     * @return bool
     */
    public function canUserAssignCategories()
    {
        trigger_deprecation('codefog/contao-news_categories', '3.5', __METHOD__.' has been deprecated, use $security->isGranted(NewsCategoriesPermissions::USER_CAN_ASSIGN_CATEGORIES) instead.');

        return $this->security->isGranted(NewsCategoriesPermissions::USER_CAN_ASSIGN_CATEGORIES);
    }

    /**
     * Get the user default categories.
     *
     * @return array
     */
    public function getUserDefaultCategories()
    {
        trigger_deprecation('codefog/contao-news_categories', '3.5', __METHOD__.' has been deprecated.');

        $user = $this->getUser();

        return \is_array($user->newscategories_default) ? $user->newscategories_default : [];
    }

    /**
     * Get the user allowed roots. Return null if the user has no limitation.
     */
    public function getUserAllowedRoots(): array|null
    {
        $user = $this->getUser();

        if ($user->isAdmin) {
            return null;
        }

        return array_map('intval', (array) $user->newscategories_roots);
    }

    /**
     * Return if the user is allowed to manage the news category.
     *
     * @param int $categoryId
     *
     * @return bool
     */
    public function isUserAllowedNewsCategory($categoryId)
    {
        trigger_deprecation('codefog/contao-news_categories', '3.5', __METHOD__.' has been deprecated, use $security->isGranted(NewsCategoriesPermissions::USER_CAN_ACCESS_CATEGORY, $categoryId) instead.');

        return $this->security->isGranted(NewsCategoriesPermissions::USER_CAN_ACCESS_CATEGORY, $categoryId);
    }

    /**
     * Add the category to allowed roots.
     *
     * @param int $categoryId
     */
    public function addCategoryToAllowedRoots($categoryId): void
    {
        trigger_deprecation('codefog/contao-news_categories', '3.5', __METHOD__.' has been deprecated, only children of allowed categories should be allowed.');

        if (null === ($roots = $this->getUserAllowedRoots())) {
            return;
        }

        $categoryId = (int) $categoryId;
        $user = $this->getUser();

        /** @var StringUtil $stringUtil */
        $stringUtil = $this->framework->getAdapter(StringUtil::class);

        // Add the permissions on group level
        if ('custom' !== $user->inherit) {
            $groups = $this->db->fetchAllAssociative('SELECT id, newscategories, newscategories_roots FROM tl_user_group WHERE id IN('.implode(',', array_map('intval', $user->groups)).')');

            foreach ($groups as $group) {
                $permissions = $stringUtil->deserialize($group['newscategories'], true);

                if (\in_array('manage', $permissions, true)) {
                    /** @var array $categoryIds */
                    $categoryIds = $stringUtil->deserialize($group['newscategories_roots'], true);
                    $categoryIds[] = $categoryId;

                    $this->db->update('tl_user_group', ['newscategories_roots' => serialize($categoryIds)], ['id' => $group['id']]);
                }
            }
        }

        // Add the permissions on user level
        if ('group' !== $user->inherit) {
            $userData = $this->db->fetchAssociative('SELECT newscategories, newscategories_roots FROM tl_user WHERE id=?', [$user->id]);
            $permissions = $stringUtil->deserialize($userData['newscategories'], true);

            if (\in_array('manage', $permissions, true)) {
                $categoryIds = $stringUtil->deserialize($userData['newscategories_roots'], true);
                $categoryIds[] = $categoryId;

                $this->db->update('tl_user', ['newscategories_roots' => serialize($categoryIds)], ['id' => $user->id]);
            }
        }

        // Add the new element to the user object
        $user->newscategories_roots = array_merge($roots, [$categoryId]);
    }

    /**
     * Get the user.
     *
     * @throws \RuntimeException
     */
    private function getUser(): BackendUser
    {
        $user = $this->security->getUser();

        if (!$user instanceof BackendUser) {
            throw new \RuntimeException('The token does not contain a back end user object');
        }

        return $user;
    }
}
