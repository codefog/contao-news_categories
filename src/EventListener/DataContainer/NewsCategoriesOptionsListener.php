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
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Input;
use Doctrine\DBAL\Connection;

#[AsCallback('tl_news', 'fields.categories.options')]
#[AsCallback('tl_news_archive', 'fields.categories.options')]
class NewsCategoriesOptionsListener
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly Connection $connection,
    ) {
    }

    public function __invoke(): array
    {
        $input = $this->framework->getAdapter(Input::class);

        // Do not generate the options for other views than listings
        if ($input->get('act') && 'select' !== $input->get('act')) {
            return [];
        }

        return $this->generateOptionsRecursively();
    }

    private function generateOptionsRecursively(int $pid = 0, string $prefix = ''): array
    {
        $options = [];
        $records = $this->connection->fetchAllAssociative('SELECT * FROM tl_news_category WHERE pid=? ORDER BY sorting', [$pid]);

        foreach ($records as $record) {
            $options[$record['id']] = $prefix.$record['title'];

            foreach ($this->generateOptionsRecursively((int) $record['id'], $record['title'].' / ') as $k => $v) {
                $options[$k] = $v;
            }
        }

        return $options;
    }
}
