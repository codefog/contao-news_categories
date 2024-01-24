<?php

declare(strict_types=1);

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2024, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\EventListener\DataContainer;

use Contao\BackendUser;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;

#[AsCallback('tl_news_category', 'config.onsubmit')]
class CategoryPermissionListener
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly Security $security,
        private readonly Connection $connection,
    ) {
    }

    public function __invoke(DataContainer $dc): void
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        $categoryId = (int) $dc->id;
        $user = $this->getUser();

        /** @var AttributeBag $bag */
        $bag = $this->requestStack->getSession()->getBag('contao_backend');
        $newRecords = $bag->get('new_records', [])['tl_news_category'] ?? [];
        $newRecords = array_map('intval', $newRecords);

        if (
            !\in_array($categoryId, $newRecords, true)
            || \in_array($categoryId, array_map('intval', $user->newscategories_roots), true)
        ) {
            return;
        }

        // Add the permissions on group level
        if ('custom' !== $user->inherit) {
            $groups = $this->connection->fetchAllAssociative(
                'SELECT id, newscategories, newscategories_roots FROM tl_user_group WHERE id IN('.implode(',', array_map('intval', $user->groups)).')',
            );

            foreach ($groups as $group) {
                $permissions = StringUtil::deserialize($group['newscategories'], true);

                if (\in_array('manage', $permissions, true)) {
                    /** @var array $categoryIds */
                    $categoryIds = StringUtil::deserialize($group['newscategories_roots'], true);
                    $categoryIds[] = $categoryId;

                    $this->connection->update('tl_user_group', ['newscategories_roots' => serialize($categoryIds)], ['id' => $group['id']]);
                }
            }
        }

        // Add the permissions on user level
        if ('group' !== $user->inherit) {
            $permissions = StringUtil::deserialize($user->newscategories, true);

            if (\in_array('manage', $permissions, true)) {
                /** @var array $categoryIds */
                $categoryIds = StringUtil::deserialize($user->newscategories_roots, true);
                $categoryIds[] = $categoryId;

                $this->connection->update('tl_user', ['newscategories_roots' => serialize($categoryIds)], ['id' => $user->id]);
            }
        }

        // Add the new element to the user object
        $user->newscategories_roots[] = $categoryId;
    }

    private function getUser(): BackendUser
    {
        $user = $this->security->getUser();

        if (!$user instanceof BackendUser) {
            throw new \RuntimeException('The token does not contain a back end user object');
        }

        return $user;
    }
}
