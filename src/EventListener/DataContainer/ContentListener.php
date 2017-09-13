<?php

namespace Codefog\NewsCategoriesBundle\EventListener\DataContainer;

use Doctrine\DBAL\Connection;

class ContentListener
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * ContentListener constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Get news modules and return them as array
     *
     * @return array
     */
    public function onGetNewsModules()
    {
        $modules = [];
        $records = $this->connection->fetchAll("SELECT m.id, m.name, t.name AS theme FROM tl_module m LEFT JOIN tl_theme t ON m.pid=t.id WHERE m.type IN ('newslist', 'newsarchive') ORDER BY t.name, m.name");

        foreach ($records as $record) {
            $modules[$record['theme']][$record['id']] = sprintf('%s (ID %s)', $record['name'], $record['id']);
        }

        return $modules;
    }
}
