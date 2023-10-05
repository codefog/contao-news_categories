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
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\FilesModel;
use Contao\Module;
use Contao\ModuleNewsArchive;
use Contao\ModuleNewsList;
use Contao\ModuleNewsReader;
use Contao\PageModel;
use Symfony\Contracts\Service\ResetInterface;
use Terminal42\DcMultilingualBundle\Model\Multilingual;

class NewsCategoriesManager implements ResetInterface
{
    private array $urlCache = [];

    private array $trailCache = [];

    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly string $projectDir,
    ) {
    }

    /**
     * Generate the category URL.
     */
    public function generateUrl(NewsCategoryModel $category, PageModel $page, bool $absolute = false): string
    {
        $cacheKey = $page->id.'-'.$category->id.'-'.($absolute ? 'abs' : 'rel');

        if (isset($this->urlCache[$cacheKey])) {
            return $this->urlCache[$cacheKey];
        }

        $page->loadDetails();
        $params = sprintf('/%s/%s', $this->getParameterName($page->rootId), $this->getCategoryAlias($category, $page));
        $this->urlCache[$cacheKey] = $absolute ? $page->getAbsoluteUrl($params) : $page->getFrontendUrl($params);

        return $this->urlCache[$cacheKey];
    }

    /**
     * Get the image.
     */
    public function getImage(NewsCategoryModel $category): FilesModel|null
    {
        if (null === ($image = $category->getImage()) || !is_file($this->projectDir.'/'.$image->path)) {
            return null;
        }

        return $image;
    }

    /**
     * Get the category alias.
     */
    public function getCategoryAlias(NewsCategoryModel $category, PageModel $page): string
    {
        if ($category instanceof Multilingual) {
            return $category->getAlias($page->language);
        }

        return $category->alias;
    }

    /**
     * Get the parameter name.
     */
    public function getParameterName(int|null $rootId = null): string
    {
        $rootId = $rootId ?: $GLOBALS['objPage']->rootId;

        if (!$rootId || null === ($rootPage = PageModel::findByPk($rootId))) {
            return 'category';
        }

        return $rootPage->newsCategories_param ?: 'category';
    }

    /**
     * Get the category target page.
     */
    public function getTargetPage(NewsCategoryModel $category): PageModel|null
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
     */
    public function getTrailIds(NewsCategoryModel $category): array
    {
        if (!isset($this->trailCache[$category->id])) {
            /** @var Database $db */
            $db = $this->framework->createInstance(Database::class);

            $ids = $db->getParentRecords($category->id, $category->getTable());
            $ids = array_map('intval', array_unique($ids));

            // Remove the current category
            unset($ids[array_search($category->id, $ids, true)]);

            $this->trailCache[$category->id] = $ids;
        }

        return $this->trailCache[$category->id];
    }

    /**
     * Return true if the category is visible for module.
     */
    public function isVisibleForModule(NewsCategoryModel $category, Module $module): bool
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

    public function reset(): void
    {
        $this->urlCache = [];
        $this->trailCache = [];
    }
}
