<?php

/*
 * News Categories Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\EventListener\DataContainer;

use Codefog\NewsCategoriesBundle\FeedGenerator;
use Contao\Automator;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use Haste\Model\Model;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FeedListener implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    /**
     * @var Connection
     */
    private $db;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * FeedListener constructor.
     *
     * @param Connection       $db
     * @param SessionInterface $session
     */
    public function __construct(Connection $db, SessionInterface $session)
    {
        $this->db = $db;
        $this->session = $session;
    }

    /**
     * On data container load callback.
     *
     * @param DataContainer $dc
     */
    public function onLoadCallback(DataContainer $dc)
    {
        switch ($dc->table) {
            case 'tl_content':
            case 'tl_news':
            case 'tl_news_archive':
            case 'tl_news_category':
                $this->generateFeed('generateFeedsByArchive');
                break;
            case 'tl_news_feed':
                $this->generateFeed('generateFeed');
                break;
        }
    }

    /**
     * On data container submit callback.
     *
     * @param DataContainer $dc
     */
    public function onSubmitCallback(DataContainer $dc)
    {
        // Schedule a news feed update
        if ('tl_news_category' === $dc->table && $dc->id) {
            /** @var Model $modelAdapter */
            $modelAdapter = $this->framework->getAdapter(Model::class);

            $newsIds = $modelAdapter->getReferenceValues('tl_news', 'categories', $dc->id);
            $newsIds = \array_map('intval', \array_unique($newsIds));

            if (\count($newsIds) > 0) {
                $archiveIds = $this->db
                    ->executeQuery('SELECT DISTINCT(pid) FROM tl_news WHERE id IN ('.\implode(',', $newsIds).')')
                    ->fetchAll(\PDO::FETCH_COLUMN, 0);

                $session = $this->session->get('news_feed_updater');
                $session = \array_merge((array) $session, $archiveIds);
                $this->session->set('news_feed_updater', \array_unique($session));
            }
        }
    }

    /**
     * Generate the feed.
     *
     * @param string $method
     */
    private function generateFeed($method)
    {
        $session = $this->session->get('news_feed_updater');

        if (!\is_array($session) || empty($session)) {
            return;
        }

        $feedGenerator = new FeedGenerator();

        foreach ($session as $id) {
            $feedGenerator->$method($id);
        }

        (new Automator())->generateSitemap();

        $this->session->set('news_feed_updater', null);
    }
}
