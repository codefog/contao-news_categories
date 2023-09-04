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

use Codefog\NewsCategoriesBundle\MultilingualHelper;
use Contao\Config;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\CoreBundle\Slug\Slug;
use Contao\DataContainer;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Terminal42\DcMultilingualBundle\Driver;

class NewsCategoryListener implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    public function __construct(
        private readonly Connection $db,
        private readonly Security $security,
        private readonly RequestStack $requestStack,
        private readonly Slug|null $slug = null,
    ) {
    }

    /**
     * On generate the category alias.
     *
     * @param string $value
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function onGenerateAlias($value, DataContainer $dc)
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

                if (MultilingualHelper::isActive() && $dc instanceof Driver) {
                    $slugOptions['locale'] = $dc->getCurrentLanguage();
                }

                $value = $this->slug->generate($title, $slugOptions);
            } else {
                $value = StringUtil::generateAlias($title);
            }
        }

        if ($dc instanceof Driver && MultilingualHelper::isActive()) {
            $exists = $this->db->fetchOne(
                "SELECT id FROM {$dc->table} WHERE alias=? AND id!=? AND {$dc->getLanguageColumn()}=?",
                [$value, $dc->activeRecord->id, $dc->getCurrentLanguage()],
            );
        } else {
            $exists = $this->db->fetchOne(
                "SELECT id FROM {$dc->table} WHERE alias=? AND id!=?",
                [$value, $dc->activeRecord->id],
            );
        }

        // Check whether the category alias exists
        if ($exists) {
            if ($autoAlias) {
                $value .= '-'.$dc->activeRecord->id;
            } else {
                throw new \RuntimeException(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $value));
            }
        }

        return $value;
    }
}
