<?php

declare(strict_types=1);

namespace Codefog\NewsCategoriesBundle\EventListener\DataContainer;

use Contao\DataContainer;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Limit the categories set in the news archive settings for tl_news.categories field.
 */
class CategoryRootListener implements ResetInterface
{
    private array $rootNodesCache = [];

    public function __construct(private readonly Connection $connection)
    {
    }

    public function __invoke(array $attributes, DataContainer $dc): array
    {
        if (!$dc->currentPid) {
            return $attributes;
        }

        $rootNodes = $this->getRootNodesForNewsArchiveId($dc->currentPid);

        if (null !== $rootNodes) {
            $attributes['rootNodes'] = $rootNodes;
        }

        return $attributes;
    }

    public function reset(): void
    {
        $this->rootNodesCache = [];
    }

    private function getRootNodesForNewsArchiveId(int $id): array|null
    {
        if (!isset($this->rootNodesCache[$id])) {
            $archive = $this->connection->fetchAssociative('SELECT limitCategories, categories FROM tl_news_archive WHERE id=?', [$id]);

            if (!$archive['limitCategories']) {
                $this->rootNodesCache[$id] = false;
            } else {
                $this->rootNodesCache[$id] = StringUtil::deserialize($archive['categories']);
            }
        }

        return false === $this->rootNodesCache[$id] ? null : $this->rootNodesCache[$id];
    }
}
