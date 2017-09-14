<?php

namespace Codefog\NewsCategoriesBundle\EventListener\DataContainer;

use Codefog\NewsCategoriesBundle\PermissionChecker;
use Contao\DataContainer;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;

class NewsListener
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var PermissionChecker
     */
    private $permissionChecker;

    /**
     * NewsListener constructor.
     *
     * @param Connection        $connection
     * @param PermissionChecker $permissionChecker
     */
    public function __construct(Connection $connection, PermissionChecker $permissionChecker)
    {
        $this->connection = $connection;
        $this->permissionChecker = $permissionChecker;
    }

    /**
     * On data container load. Limit the categories set in the news archive settings
     *
     * @param DataContainer $dc
     */
    public function onLoadCallback(DataContainer $dc)
    {
        if (!$dc->id) {
            return;
        }

        $categories = $this->connection->fetchColumn('SELECT categories FROM tl_news_archive WHERE limitCategories=1 AND id=(SELECT pid FROM tl_news WHERE id=?)', [$dc->id]);

        if (!$categories || count($categories = StringUtil::deserialize($categories, true)) === 0) {
            return;
        }

        $GLOBALS['TL_DCA'][$dc->table]['fields']['categories']['rootNodes'] = $categories;
    }

    /**
     * On copy the record. Duplicate also the relations
     *
     * @param int           $id
     * @param DataContainer $dc
     */
    public function onCopyCallback($id, DataContainer $dc)
    {
        $categories = $this->connection->fetchAll('SELECT category_id FROM tl_news_categories WHERE news_id=?', [$dc->id]);

        foreach ($categories as $category) {
            $this->connection->insert('tl_news_categories', [
                'category_id' => $category['category_id'],
                'news_id' => $id,
            ]);
        }
    }

    /**
     * On submit record. Update the category relations
     *
     * @param DataContainer $dc
     */
    public function onSubmitCallback(DataContainer $dc)
    {
        // Use the default categories if the user is not allowed to edit the field directly
        if (!$this->permissionChecker->canUserAssignNewsCategories()) {
            // Return if the record is not new
            if ($dc->activeRecord->tstamp) {
                return;
            }

            $categories = $this->permissionChecker->getUserDefaultCategories();
        } else {
            // Otherwise just use the categories of submitted record
            $categories = StringUtil::deserialize($dc->activeRecord->categories, true);
        }

        $this->deleteCategories($dc->id);

        if (count($categories) === 0) {
            return;
        }

        // Add new categories
        foreach ($categories as $category) {
            $this->connection->insert('tl_news_categories', [
                'category_id' => $category,
                'news_id' => $dc->id,
            ]);
        }

        $this->connection->update('tl_news', ['categories' => serialize($categories)], ['id' => $dc->id]);
    }

    /**
     * On delete the record
     *
     * @param DataContainer $dc
     */
    public function onDeleteCallback(DataContainer $dc)
    {
        $this->deleteCategories($dc->id);
    }

    /**
     * Delete the category relations
     *
     * @param int $newsId
     */
    private function deleteCategories($newsId)
    {
        $this->connection->delete('tl_news_categories', ['news_id' => $newsId]);
    }
}
