<?php

declare(strict_types=1);

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2023, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\EventListener\DataContainer;

use Codefog\HasteBundle\DcaRelationsManager;
use Codefog\NewsCategoriesBundle\Security\NewsCategoriesPermissions;
use Contao\BackendUser;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Adds the default user categories for new records.
 */
#[AsCallback('tl_news', 'config.onsubmit')]
class NewsDefaultCategoriesListener
{
    public function __construct(
        private readonly DcaRelationsManager $dcaRelationsManager,
        private readonly Security $security,
    ) {
    }

    public function __invoke(DataContainer $dc): void
    {
        // Return if the user is allowed to assign categories or the record is not new
        if ($dc->activeRecord->tstamp > 0 || $this->security->isGranted(NewsCategoriesPermissions::USER_CAN_ASSIGN_CATEGORIES)) {
            return;
        }

        $user = $this->security->getUser();

        if (!$user instanceof BackendUser || empty($user->newscategories_default)) {
            return;
        }

        $dc->field = 'categories';

        $this->dcaRelationsManager->updateRelatedRecords((array) $user->newscategories_default, $dc);

        // Reset back the field property
        $dc->field = null;
    }
}
