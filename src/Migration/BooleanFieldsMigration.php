<?php

declare(strict_types=1);

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2024, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\BooleanType;

class BooleanFieldsMigration extends AbstractMigration
{
    private static array $fields = [
        'tl_content' => ['news_filterPreserve', 'news_filterCategories'],
        'tl_module' => ['news_resetCategories', 'news_filterPreserve', 'news_relatedCategories', 'news_filterCategories', 'news_customCategories'],
        'tl_news_archive' => ['limitCategories'],
        'tl_news_category' => ['excludeInRelated', 'hideInReader', 'hideInList', 'published'],
    ];

    public function __construct(private readonly Connection $db)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->db->createSchemaManager();

        if (!$schemaManager->tablesExist(array_keys(self::$fields))) {
            return false;
        }

        foreach (self::$fields as $table => $fields) {
            foreach ($fields as $field) {
                if ($this->needsMigration($table, $field)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function run(): MigrationResult
    {
        foreach (self::$fields as $table => $fields) {
            foreach ($fields as $field) {
                if ($this->needsMigration($table, $field)) {
                    $this->db->executeQuery("UPDATE $table SET $field = '0' WHERE $field = ''");
                }
            }
        }

        return $this->createResult(true);
    }

    private function needsMigration(string $table, string $field): bool
    {
        $columns = $this->db->createSchemaManager()->listTableColumns($table);
        $column = $columns[strtolower($field)] ?? null;

        if (null === $column || $column->getType() instanceof BooleanType) {
            return false;
        }

        return (int) $this->db->fetchOne("SELECT COUNT(*) FROM $table WHERE $field = ''") > 0;
    }
}
