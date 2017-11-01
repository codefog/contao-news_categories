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
    private $db;

    /**
     * @var PermissionChecker
     */
    private $permissionChecker;

    /**
     * NewsListener constructor.
     *
     * @param Connection        $db
     * @param PermissionChecker $permissionChecker
     */
    public function __construct(Connection $db, PermissionChecker $permissionChecker)
    {
        $this->db = $db;
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

        $categories = $this->db->fetchColumn('SELECT categories FROM tl_news_archive WHERE limitCategories=1 AND id=(SELECT pid FROM tl_news WHERE id=?)', [$dc->id]);

        if (!$categories || count($categories = StringUtil::deserialize($categories, true)) === 0) {
            return;
        }

        $GLOBALS['TL_DCA'][$dc->table]['fields']['categories']['rootNodes'] = $categories;
    }

    /**
     * On submit record. Update the category relations
     *
     * @param DataContainer $dc
     */
    public function onSubmitCallback(DataContainer $dc)
    {
        // Use the default categories if the user is not allowed to edit the field directly
        if (!$this->permissionChecker->canUserAssignCategories()) {
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
            $this->db->insert('tl_news_categories', [
                'category_id' => $category,
                'news_id' => $dc->id,
            ]);
        }

        $this->db->update('tl_news', ['categories' => serialize($categories)], ['id' => $dc->id]);
    }

    /**
     * Delete the category relations
     *
     * @param int $newsId
     */
    private function deleteCategories($newsId)
    {
        $this->db->delete('tl_news_categories', ['news_id' => $newsId]);
    }
}
