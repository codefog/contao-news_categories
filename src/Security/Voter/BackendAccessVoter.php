<?php

declare(strict_types=1);

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2024, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\Security\Voter;

use Codefog\NewsCategoriesBundle\Security\NewsCategoriesPermissions;
use Contao\BackendUser;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\CacheableVoterInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * This voter priority-overrides the regular Contao BackendAccessVoter
 * to vote on tl_user.newscategories_roots recursively.
 */
#[AutoconfigureTag('security.voter', ['priority' => 8])]
class BackendAccessVoter implements CacheableVoterInterface, ResetInterface
{
    /**
     * @var array<string, array<int>>
     */
    private array $childRecords = [];

    public function __construct(private readonly ContaoFramework $framework)
    {
    }

    public function supportsAttribute(string $attribute): bool
    {
        return NewsCategoriesPermissions::USER_CAN_ACCESS_CATEGORY === $attribute;
    }

    public function supportsType(string $subjectType): bool
    {
        return true;
    }

    public function vote(TokenInterface $token, mixed $subject, array $attributes)
    {
        $user = $token->getUser();

        if (!$user instanceof BackendUser) {
            return VoterInterface::ACCESS_DENIED;
        }

        if ($user->isAdmin || empty($user->newscategories_roots)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        $ids = $this->getChildRecords((array) $user->newscategories_roots);

        return \in_array((int) $subject, $ids, true) ? VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED;
    }

    public function reset(): void
    {
        $this->childRecords = [];
    }

    private function getChildRecords(array $ids): array
    {
        $key = implode(',', $ids);

        if (!isset($this->childRecords[$key])) {
            $this->framework->initialize();

            $this->childRecords[$key] = Database::getInstance()->getChildRecords($ids, 'tl_news_category', false, $ids);
        }

        return $this->childRecords[$key];
    }
}
