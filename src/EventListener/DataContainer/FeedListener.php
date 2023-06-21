<?php

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\EventListener\DataContainer;

use Codefog\HasteBundle\Model\DcaRelationsModel;
use Codefog\NewsCategoriesBundle\FeedGenerator;
use Contao\Automator;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RequestStack;

class FeedListener implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    /**
     * @var Connection
     */
    private $db;

    private RequestStack $requestStack;

    public function __construct(Connection $db, RequestStack $requestStack)
    {
        $this->db = $db;
        $this->requestStack = $requestStack;
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
            /** @var DcaRelationsModel $modelAdapter */
            $modelAdapter = $this->framework->getAdapter(DcaRelationsModel::class);

            $newsIds = $modelAdapter->getReferenceValues('tl_news', 'categories', $dc->id);
            $newsIds = \array_map('intval', \array_unique($newsIds));

            if (\count($newsIds) > 0) {
                $archiveIds = $this->db->fetchFirstColumn('SELECT DISTINCT(pid) FROM tl_news WHERE id IN ('.\implode(',', $newsIds).')');

                $session = $this->requestStack->getSession()->get('news_feed_updater');
                $session = \array_merge((array) $session, $archiveIds);
                $this->requestStack->getSession()->set('news_feed_updater', \array_unique($session));
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
        $session = $this->requestStack->getSession()->get('news_feed_updater');

        if (!\is_array($session) || empty($session)) {
            return;
        }

        $feedGenerator = new FeedGenerator();

        foreach ($session as $id) {
            $feedGenerator->$method($id);
        }

        (new Automator())->generateSitemap();

        $this->requestStack->getSession()->set('news_feed_updater', null);
    }
}
