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

use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCallback('tl_news', 'list.label.label')]
class NewsLabelListener
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function __invoke(array $row, string $label): string
    {
        $categories = NewsCategoryModel::findPublishedByNews($row['id']);

        if ($categories) {
            $label .= \sprintf(
                '<span style="position:relative;top:-1px;margin-left:3px" title="%s %s"><img src="bundles/codefognewscategories/icon.svg" alt=""></span>',
                $this->translator->trans('MSC.newsCategories', [], 'contao_default'),
                implode(', ', $categories->fetchEach('title')),
            );
        }

        return $label;
    }
}
