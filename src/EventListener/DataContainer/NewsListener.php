<?php

declare(strict_types=1);

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\EventListener\DataContainer;

use Codefog\HasteBundle\DcaRelationsManager;
use Codefog\NewsCategoriesBundle\Security\NewsCategoriesPermissions;
use Contao\BackendUser;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\DataContainer;
use Contao\Input;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\SecurityBundle\Security;

class NewsListener implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    /**
     * NewsListener constructor.
     */
    public function __construct(
        private readonly Connection $db,
        private readonly DcaRelationsManager $dcaRelationsManager,
        private readonly Security $security,
    ) {
    }

    /**
     * On submit record. Adds the default user categories for new records.
     */
    public function onSubmitCallback(DataContainer $dc): void
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

    /**
     * On categories options callback.
     *
     * @return array
     */
    public function onCategoriesOptionsCallback()
    {
        /** @var Input $input */
        $input = $this->framework->getAdapter(Input::class);

        // Do not generate the options for other views than listings
        if ($input->get('act') && 'select' !== $input->get('act')) {
            return [];
        }

        return $this->generateOptionsRecursively();
    }

    /**
     * Generate the options recursively.
     *
     * @param int    $pid
     * @param string $prefix
     *
     * @return array
     */
    private function generateOptionsRecursively($pid = 0, $prefix = '')
    {
        $options = [];
        $records = $this->db->fetchAllAssociative('SELECT * FROM tl_news_category WHERE pid=? ORDER BY sorting', [$pid]);

        foreach ($records as $record) {
            $options[$record['id']] = $prefix.$record['title'];

            foreach ($this->generateOptionsRecursively($record['id'], $record['title'].' / ') as $k => $v) {
                $options[$k] = $v;
            }
        }

        return $options;
    }
}
