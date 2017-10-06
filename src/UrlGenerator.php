<?php

namespace Codefog\NewsCategoriesBundle;

use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\PageModel;
use NewsCategories\NewsCategories;

class UrlGenerator implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    /**
     * Generate the category URL
     *
     * @param NewsCategory $category
     * @param PageModel $page
     * @param boolean $absolute
     *
     * @return string
     */
    public function generateUrl(NewsCategory $category, PageModel $page, $absolute = false)
    {
        $page->loadDetails();
        $params = '/' . NewsCategories::getParameterName($page->rootId) . '/' . $category->getModel()->alias;

        return $absolute ? $page->getAbsoluteUrl($params) : $page->getFrontendUrl($params);
    }

    /**
     * Get the category target page
     *
     * @param NewsCategory $category
     *
     * @return PageModel|null
     */
    public function getTargetPage(NewsCategory $category)
    {
        $model = $category->getModel();
        $pageId = $model->jumpTo;

        // Inherit the page from parent if there is none set
        if (!$pageId) {
            $pid = $model->pid;

            do {
                /** @var NewsCategoryModel $parent */
                $parent = $model->findByPk($pid);

                if ($parent !== null) {
                    $pid = $parent->pid;
                    $pageId = $parent->jumpTo;
                }
            } while ($pid && !$pageId);
        }

        /** @var PageModel $pageAdapter */
        $pageAdapter = $this->framework->getAdapter(PageModel::class);

        return $pageAdapter->findByPk($pageId);
    }
}
