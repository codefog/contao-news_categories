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

use Contao\Config;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Slug\Slug;
use Contao\DataContainer;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Terminal42\DcMultilingualBundle\Driver;

#[AsCallback('tl_news_categories', 'fields.alias.save')]
class NewsCategoryAliasListener
{
    public function __construct(
        private readonly Connection $db,
        private readonly Slug|null $slug = null,
    ) {
    }

    public function __invoke(string $value, DataContainer $dc): string
    {
        $autoAlias = false;

        // Generate alias if there is none
        if (!$value) {
            $autoAlias = true;
            $title = $dc->activeRecord->frontendTitle ?: $dc->activeRecord->title;

            if (null !== $this->slug) {
                $slugOptions = [];

                if (!empty($validChars = Config::get('news_categorySlugSetting'))) {
                    $slugOptions['validChars'] = $validChars;
                }

                if ($dc instanceof Driver) {
                    $slugOptions['locale'] = $dc->getCurrentLanguage();
                }

                $value = $this->slug->generate($title, $slugOptions);
            } else {
                $value = StringUtil::generateAlias($title);
            }
        }

        if ($dc instanceof Driver) {
            $exists = $this->db->fetchOne(
                "SELECT id FROM {$dc->table} WHERE alias=? AND id!=? AND {$dc->getLanguageColumn()}=?",
                [$value, $dc->id, $dc->getCurrentLanguage()],
            );
        } else {
            $exists = $this->db->fetchOne(
                "SELECT id FROM {$dc->table} WHERE alias=? AND id!=?",
                [$value, $dc->id],
            );
        }

        // Check whether the category alias exists
        if ($exists) {
            if ($autoAlias) {
                $value .= '-'.$dc->id;
            } else {
                throw new \RuntimeException(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $value));
            }
        }

        return $value;
    }
}
