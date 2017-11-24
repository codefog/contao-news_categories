<?php

/*
 * News Categories Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\EventListener\DataContainer;

use Doctrine\DBAL\Connection;

class ContentListener
{
    /**
     * @var Connection
     */
    private $db;

    /**
     * ContentListener constructor.
     *
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Get news modules and return them as array.
     *
     * @return array
     */
    public function onGetNewsModules()
    {
        $modules = [];
        $records = $this->db->fetchAll("SELECT m.id, m.name, t.name AS theme FROM tl_module m LEFT JOIN tl_theme t ON m.pid=t.id WHERE m.type IN ('newslist', 'newsarchive') ORDER BY t.name, m.name");

        foreach ($records as $record) {
            $modules[$record['theme']][$record['id']] = sprintf('%s (ID %s)', $record['name'], $record['id']);
        }

        return $modules;
    }
}
