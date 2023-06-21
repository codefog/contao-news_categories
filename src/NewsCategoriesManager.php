<?php

declare(strict_types=1);

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
use Contao\FilesModel;
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
     * @var array
     */
    private $urlCache = [];

    /**
     * Generate the category URL.
     *
     * @param bool $absolute
     *
     * @return string
     */
    public function generateUrl(NewsCategoryModel $category, PageModel $page, $absolute = false)
    {
        $page->loadDetails();
        $cacheKey = $page->id.'-'.($absolute ? 'abs' : 'rel');

        if (!isset($this->urlCache[$cacheKey])) {
            $params = '/%s/%s';
            $this->urlCache[$cacheKey] = $absolute ? $page->getAbsoluteUrl($params) : $page->getFrontendUrl($params);
        }

        return sprintf($this->urlCache[$cacheKey], $this->getParameterName($page->rootId), $this->getCategoryAlias($category, $page));
    }

    /**
     * Get the image.
     *
     * @return FilesModel|null
     */
    public function getImage(NewsCategoryModel $category)
    {
        if (null === ($image = $category->getImage()) || !is_file(TL_ROOT.'/'.$image->path)) {
            return null;
        }

        return $image;
    }

    /**
     * Get the category alias.
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
            static $pageCache = [];

            if (!isset($pageCache[$pageId])) {
                /** @var PageModel $pageAdapter */
                $pageAdapter = $this->framework->getAdapter(PageModel::class);
                $pageCache[$pageId] = $pageAdapter->findPublishedById($pageId);
            }

            return $pageCache[$pageId];
        }

        return null;
    }

    /**
     * Get the category trail IDs.
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
            $ids = array_map('intval', array_unique($ids));

            // Remove the current category
            unset($ids[array_search($category->id, $ids, true)]);

            $cache[$category->id] = $ids;
        }

        return $cache[$category->id];
    }

    /**
     * Return true if the category is visible for module.
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
