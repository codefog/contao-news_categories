<?php

declare(strict_types=1);

namespace Codefog\NewsCategoriesBundle\Security\Voter\DataContainer;

use Codefog\NewsCategoriesBundle\Security\NewsCategoriesPermissions;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\CoreBundle\Security\DataContainer\CreateAction;
use Contao\CoreBundle\Security\DataContainer\DeleteAction;
use Contao\CoreBundle\Security\DataContainer\ReadAction;
use Contao\CoreBundle\Security\DataContainer\UpdateAction;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\CacheableVoterInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class NewsCategoriesVoter implements CacheableVoterInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function supportsAttribute(string $attribute): bool
    {
        return $attribute === ContaoCorePermissions::DC_PREFIX.'tl_news_category';
    }

    public function supportsType(string $subjectType): bool
    {
        return \in_array($subjectType, [CreateAction::class, ReadAction::class, UpdateAction::class, DeleteAction::class], true);
    }

    public function vote(TokenInterface $token, $subject, array $attributes): int
    {
        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

            $isGranted = match (true) {
                $subject instanceof ReadAction => $this->canRead($subject),
                $subject instanceof CreateAction,
                $subject instanceof UpdateAction,
                $subject instanceof DeleteAction => $this->canWrite($subject),
                default => false,
            };

            return $isGranted ? VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    private function canRead(ReadAction $action): bool
    {
        if ($this->isNew($action)) {
            return true;
        }

        return $this->security->isGranted(NewsCategoriesPermissions::USER_CAN_ACCESS_CATEGORY, $action->getCurrentId());
    }

    private function canWrite(CreateAction|DeleteAction|UpdateAction $action): bool
    {
        if (!$this->security->isGranted(NewsCategoriesPermissions::USER_CAN_MANAGE_CATEGORIES)) {
            return false;
        }

        if ($this->isNew($action)) {
            return true;
        }

        // Check if user can access current category to delete
        if ($action instanceof DeleteAction) {
            return $this->security->isGranted(NewsCategoriesPermissions::USER_CAN_ACCESS_CATEGORY, $action->getCurrentId());
        }

        // Check the parent ID "to-be" whether the user can write into a category
        if ($action->getNewPid() > 0 && !$this->security->isGranted(NewsCategoriesPermissions::USER_CAN_ACCESS_CATEGORY, $action->getNewPid())) {
            return false;
        }

        if ($action instanceof UpdateAction) {
            return $this->security->isGranted(NewsCategoriesPermissions::USER_CAN_ACCESS_CATEGORY, $action->getCurrentId());
        }

        // Allow new records without PID so the copy operation is available
        return true;
    }

    private function isNew(CreateAction|DeleteAction|ReadAction|UpdateAction $action): bool
    {
        $recordId = $action instanceof CreateAction ? $action->getNewId() : $action->getCurrentId();

        /** @var AttributeBag $bag */
        $bag = $this->requestStack->getSession()->getBag('contao_backend');
        $newRecords = $bag->get('new_records', [])['tl_news_category'] ?? [];
        $newRecords = array_map('intval', $newRecords);

        return \in_array((int) $recordId, $newRecords, true);
    }
}
