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

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Slug\ValidCharacters;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(bind: ['$validCharacters' => '@contao.slug.valid_characters'])]
#[AsCallback('tl_settings', 'fields.news_categorySlugSetting.options')]
class SettingSlugOptionsListener
{
    public function __construct(private readonly ValidCharacters $validCharacters)
    {
    }

    public function __invoke(): array
    {
        return $this->validCharacters->getOptions();
    }
}
