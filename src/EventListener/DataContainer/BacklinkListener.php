<?php

declare(strict_types=1);

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2026, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsCallback('tl_news_category', 'config.onload')]
class BacklinkListener
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function __invoke(): void
    {
        if ($this->requestStack->getCurrentRequest()?->query->has('picker')) {
            return;
        }

        $GLOBALS['TL_DCA']['tl_news_category']['config']['backlink'] = 'do=news';
    }
}
