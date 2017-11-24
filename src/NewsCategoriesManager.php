<?php

namespace Codefog\NewsCategoriesBundle;

use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\Database;
use Contao\Module;
use Contao\ModuleNewsArchive;
use Contao\ModuleNewsList;
use Contao\ModuleNewsReader;
use Contao\PageModel;
use Terminal42\DcMultilingualBundle\Model\Multilingual;

class NewsCategoriesManager implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    /**
     * Generate the category URL
     *
     * @param NewsCategoryModel $category
     * @param PageModel    $page
     * @param boolean      $absolute
     *
     * @return string
     */
    public function generateUrl(NewsCategoryModel $category, PageModel $page, $absolute = false)
    {
        $page->loadDetails();

        // Get the alias
        if ($category instanceof Multilingual) {
            $alias = $category->getAlias($page->language);
        } else {
            $alias = $category->alias;
        }

        $params = '/' . $this->getParameterName($page->rootId) . '/' . $alias;

        return $absolute ? $page->getAbsoluteUrl($params) : $page->getFrontendUrl($params);
    }

    /**
     * Get the image
     *
     * @param NewsCategoryModel $category
     *
     * @return \Contao\FilesModel|null
     */
    public function getImage(NewsCategoryModel $category)
    {
        if (($image = $category->getImage()) === null || !is_file(TL_ROOT . '/' . $image->path)) {
            return null;
        }

        return $image;
    }

    /**
     * Get the parameter name
     *
     * @param int|null $rootId
     *
     * @return string
     */
    public function getParameterName($rootId = null)
    {
        $rootId = $rootId ?: $GLOBALS['objPage']->rootId;

        if (!$rootId || ($rootPage = PageModel::findByPk($rootId)) === null) {
            return '';
        }

        return $rootPage->newsCategories_param ?: 'category';
    }

    /**
     * Get the category target page
     *
     * @param NewsCategoryModel $category
     *
     * @return PageModel|null
     */
    public function getTargetPage(NewsCategoryModel $category)
    {
        $pageId = $category->jumpTo;

        // Inherit the page from parent if there is none set
        if (!$pageId) {
            $pid = $category->pid;

            do {
                /** @var NewsCategoryModel $parent */
                $parent = $category->findByPk($pid);

                if ($parent !== null) {
                    $pid = $parent->pid;
                    $pageId = $parent->jumpTo;
                }
            } while ($pid && !$pageId);
        }

        // Get the page model
        if ($pageId) {
            /** @var PageModel $pageAdapter */
            $pageAdapter = $this->framework->getAdapter(PageModel::class);

            return $pageAdapter->findPublishedById($pageId);
        }

        return null;
    }

    /**
     * Get the category trail IDs
     *
     * @param NewsCategoryModel $category
     *
     * @return array
     */
    public function getTrailIds(NewsCategoryModel $category)
    {
        static $ids;

        if (!is_array($ids)) {
            /** @var Database $db */
            $db = $this->framework->createInstance(Database::class);

            $ids = $db->getParentRecords($category->id, $category->getTable());
            $ids = array_map('intval', array_unique($ids));

            // Remove the current category
            unset($ids[array_search($category->id, $ids)]);
        }

        return $ids;
    }

    /**
     * Return true if the category is visible for module
     *
     * @param NewsCategoryModel $category
     * @param Module            $module
     *
     * @return bool
     */
    public function isVisibleForModule(NewsCategoryModel $category, Module $module)
    {
        // List or archive module
        if ($category->hideInList && ($module instanceof ModuleNewsList || $module instanceof ModuleNewsArchive)) {
            return false;
        }

        // Reader module
        if ($category->hideInReader && $module instanceof ModuleNewsReader) {
            return false;
        }

        return true;
    }
}
