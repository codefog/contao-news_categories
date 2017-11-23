<?php

namespace Codefog\NewsCategoriesBundle;

use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\PageModel;
use Terminal42\DcMultilingualBundle\Model\Multilingual;

class UrlGenerator implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

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
     * Generate the category URL
     *
     * @param NewsCategory $category
     * @param PageModel    $page
     * @param boolean      $absolute
     *
     * @return string
     */
    public function generateUrl(NewsCategory $category, PageModel $page, $absolute = false)
    {
        $page->loadDetails();
        $model = $category->getModel();

        // Get the alias
        if ($model instanceof Multilingual) {
            $alias = $model->getAlias($page->language);
        } else {
            $alias = $model->alias;
        }

        $params = '/' . $this->getParameterName($page->rootId) . '/' . $alias;

        return $absolute ? $page->getAbsoluteUrl($params) : $page->getFrontendUrl($params);
    }
}
