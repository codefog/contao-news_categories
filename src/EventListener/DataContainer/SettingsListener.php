<?php

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
    private $validCharacters;

    public function __construct(ValidCharacters $validCharacters = null)
    {
        $this->validCharacters = $validCharacters;
    }

    /**
     * On slug setting options callback
     *
     * @return array
     */
    public function onSlugSettingOptionsCallback()
    {
        return $this->validCharacters->getOptions();
    }
}
