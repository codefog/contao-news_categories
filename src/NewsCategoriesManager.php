<?php

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

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
     * Generate the category URL.
     *
     * @param NewsCategoryModel $category
     * @param PageModel         $page
     * @param bool              $absolute
     *
     * @return string
     */
    public function generateUrl(NewsCategoryModel $category, PageModel $page, $absolute = false)
    {
        $page->loadDetails();

        $params = '/'.$this->getParameterName($page->rootId).'/'.$this->getCategoryAlias($category, $page);

        return $absolute ? $page->getAbsoluteUrl($params) : $page->getFrontendUrl($params);
    }

    /**
     * Get the image.
     *
     * @param NewsCategoryModel $category
     *
     * @return \Contao\FilesModel|null
     */
    public function getImage(NewsCategoryModel $category)
    {
        if (null === ($image = $category->getImage()) || !\is_file(TL_ROOT.'/'.$image->path)) {
            return null;
        }

        return $image;
    }

    /**
     * Get the category alias
     *
     * @param NewsCategoryModel $category
     * @param PageModel         $page
     *
     * @return string
     */
    public function getCategoryAlias(NewsCategoryModel $category, PageModel $page)
    {
        if ($category instanceof Multilingual) {
            return $category->getAlias($page->language);
        }

        return $category->alias;
    }

    /**
     * Get the parameter name.
     *
     * @param int|null $rootId
     *
     * @return string
     */
    public function getParameterName($rootId = null)
    {
        $rootId = $rootId ?: $GLOBALS['objPage']->rootId;

        if (!$rootId || null === ($rootPage = PageModel::findByPk($rootId))) {
            return '';
        }

        return $rootPage->newsCategories_param ?: 'category';
    }

    /**
     * Get the category target page.
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

                if (null !== $parent) {
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
     * Get the category trail IDs.
     *
     * @param NewsCategoryModel $category
     *
     * @return array
     */
    public function getTrailIds(NewsCategoryModel $category)
    {
        static $cache;

        if (!isset($cache[$category->id])) {
            /** @var Database $db */
            $db = $this->framework->createInstance(Database::class);

            $ids = $db->getParentRecords($category->id, $category->getTable());
            $ids = \array_map('intval', \array_unique($ids));

            // Remove the current category
            unset($ids[\array_search($category->id, $ids, true)]);

            $cache[$category->id] = $ids;
        }

        return $cache[$category->id];
    }

    /**
     * Return true if the category is visible for module.
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
