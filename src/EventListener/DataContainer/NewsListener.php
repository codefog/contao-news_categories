<?php

/*
 * News Categories Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\EventListener\DataContainer;

use Codefog\NewsCategoriesBundle\PermissionChecker;
use Contao\DataContainer;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Haste\Model\Relations;

class NewsListener
{
    /**
     * @var Connection
     */
    private $db;

    /**
     * @var PermissionChecker
     */
    private $permissionChecker;

    /**
     * NewsListener constructor.
     *
     * @param Connection        $db
     * @param PermissionChecker $permissionChecker
     */
    public function __construct(Connection $db, PermissionChecker $permissionChecker)
    {
        $this->db = $db;
        $this->permissionChecker = $permissionChecker;
    }

    /**
     * On data container load. Limit the categories set in the news archive settings.
     *
     * @param DataContainer $dc
     */
    public function onLoadCallback(DataContainer $dc)
    {
        if (!$dc->id) {
            return;
        }

        $categories = $this->db->fetchColumn('SELECT categories FROM tl_news_archive WHERE limitCategories=1 AND id=(SELECT pid FROM tl_news WHERE id=?)', [$dc->id]);

        if (!$categories || 0 === \count($categories = StringUtil::deserialize($categories, true))) {
            return;
        }

        $GLOBALS['TL_DCA'][$dc->table]['fields']['categories']['rootNodes'] = $categories;
    }

    /**
     * On submit record. Update the category relations.
     *
     * @param DataContainer $dc
     */
    public function onSubmitCallback(DataContainer $dc)
    {
        // Return if the user is allowed to assign categories or the record is not new
        if ($this->permissionChecker->canUserAssignCategories() || $dc->activeRecord->tstamp > 0) {
            return;
        }

        $dc->field = 'categories';

        $relations = new Relations();
        $relations->updateRelatedRecords($this->permissionChecker->getUserDefaultCategories(), $dc);

        // Reset back the field property
        $dc->field = null;
    }
}
