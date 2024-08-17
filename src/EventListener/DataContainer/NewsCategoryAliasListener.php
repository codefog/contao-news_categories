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

use Contao\Config;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Slug\Slug;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use Terminal42\DcMultilingualBundle\Driver;

class NewsCategoryAliasListener
{
    public function __construct(
        private readonly Connection $db,
        private readonly Slug $slug,
    ) {
    }

    #[AsCallback('tl_news_category', 'fields.alias.save')]
    public function validateAlias(string $value, DataContainer $dc): string
    {
        if ('' !== $value && $this->aliasExists($value, $dc)) {
            throw new \RuntimeException(\sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $value));
        }

        return $value;
    }

    #[AsCallback('tl_news_category', 'config.onbeforesubmit')]
    public function generateAlias(array $values, DataContainer $dc): array
    {
        $currentRecord = $this->getCurrentRecord($dc);
        $title = $values['frontendTitle'] ?? $currentRecord['frontendTitle'] ?: ($values['title'] ?? $currentRecord['title']);

        if ('' !== ($values['alias'] ?? $currentRecord['alias'] ?? '')) {
            return $values;
        }

        $slugOptions = [];

        if (!empty($validChars = Config::get('news_categorySlugSetting'))) {
            $slugOptions['validChars'] = $validChars;
        }

        if ($dc instanceof Driver) {
            $slugOptions['locale'] = $dc->getCurrentLanguage();
        }

        $value = $this->slug->generate($title, $slugOptions);

        if ($this->aliasExists($value, $dc)) {
            $value .= '-'.$dc->id;
        }

        $values['alias'] = $value;

        return $values;
    }

    private function aliasExists(string $value, DataContainer $dc): bool
    {
        $query = "SELECT id FROM {$dc->table} WHERE alias=?";
        $params = [$value];

        if ($dc instanceof Driver) {
            if ('' !== $dc->getCurrentLanguage()) {
                $query .= " AND {$dc->getPidColumn()}!=? AND {$dc->getLanguageColumn()}=?";
            } else {
                $query .= " AND id!=? AND {$dc->getLanguageColumn()}=?";
            }

            $params[] = $dc->id;
            $params[] = $dc->getCurrentLanguage();
        } else {
            $query .= ' AND id!=?';
            $params[] = $dc->id;
        }

        return false !== $this->db->fetchOne($query, $params);
    }

    private function getCurrentRecord(DataContainer $dc): array
    {
        if ($dc instanceof Driver && '' !== $dc->getCurrentLanguage()) {
            return $this->db->fetchAssociative(
                "SELECT * FROM {$dc->table} WHERE {$dc->getPidColumn()}=? AND {$dc->getLanguageColumn()}=?",
                [$dc->id, $dc->getCurrentLanguage()],
            );
        }

        return $dc->getCurrentRecord();
    }
}
