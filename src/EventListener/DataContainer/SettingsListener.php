<?php

declare(strict_types=1);

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2020, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\EventListener\DataContainer;

use Contao\CoreBundle\Slug\ValidCharacters;

class SettingsListener
{
    public function __construct(private readonly ValidCharacters|null $validCharacters = null)
    {
    }

    /**
     * On slug setting options callback.
     *
     * @return array
     */
    public function onSlugSettingOptionsCallback()
    {
        return $this->validCharacters->getOptions();
    }
}
