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

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Doctrine\DBAL\Connection;

/**
 * Get news modules and return them as array.
 */
#[AsCallback('tl_content', 'fields.news_module.options')]
class ContentNewsModuleOptionsListener
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function __invoke(): array
    {
        $modules = [];
        $records = $this->db->fetchAllAssociative("SELECT m.id, m.name, t.name AS theme FROM tl_module m LEFT JOIN tl_theme t ON m.pid=t.id WHERE m.type IN ('newslist', 'newsarchive') ORDER BY t.name, m.name");

        foreach ($records as $record) {
            $modules[$record['theme']][$record['id']] = \sprintf('%s (ID %s)', $record['name'], $record['id']);
        }

        return $modules;
    }
}
