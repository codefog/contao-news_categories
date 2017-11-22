<?php

namespace Codefog\NewsCategoriesBundle\EventListener;

use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Codefog\NewsCategoriesBundle\NewsCategory;
use Codefog\NewsCategoriesBundle\UrlGenerator;
use Contao\Controller;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\FrontendTemplate;
use Contao\Module;
use Contao\PageModel;
use Contao\StringUtil;

class TemplateListener implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    /**
     * @var UrlGenerator
     */
    private $urlGenerator;

    /**
     * TemplateListener constructor.
     *
     * @param UrlGenerator $urlGenerator
     */
    public function __construct(UrlGenerator $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * On parse the articles
     *
     * @param FrontendTemplate $template
     * @param array            $data
     * @param Module           $module
     */
    public function onParseArticles(FrontendTemplate $template, array $data, Module $module)
    {
        /** @var NewsCategoryModel $newsCategoryModelAdapter */
        $newsCategoryModelAdapter = $this->framework->getAdapter(NewsCategoryModel::class);

        if (($models = $newsCategoryModelAdapter->findPublishedByNews($data['id'])) === null) {
            return;
        }

        $categories = [];

        /** @var NewsCategoryModel $model */
        foreach ($models as $model) {
            $categories[] = new NewsCategory($model);
        }

        $this->addCategoriesToTemplate($template, $module, $categories);
    }

    /**
     * Add categories to the template
     *
     * @param FrontendTemplate $template
     * @param Module           $module
     * @param array            $categories
     */
    private function addCategoriesToTemplate(FrontendTemplate $template, Module $module, array $categories)
    {
        $data = [];
        $list = [];
        $cssClasses = trimsplit(' ', $template->class);

        /** @var NewsCategory $category */
        foreach ($categories as $category) {
            // Skip the categories not eligible for the current module
            if (!$category->isVisibleForModule($module)) {
                continue;
            }

            $model = $category->getModel();

            // Add category to data and list
            $data[$model->id] = $this->generateCategoryData($category, $module);
            $list[$model->id] = $category->getTitle();

            // Add the category CSS classes to news class
            $cssClasses = array_merge($cssClasses, trimsplit(' ', $category->getCssClass()));
        }

        // Sort the categories data alphabetically
        uasort($data, function($a, $b) {
            return strnatcasecmp($a['name'], $b['name']);
        });

        // Sort the category list alphabetically
        asort($list);

        $template->categories = $data;
        $template->categoriesList = $list;
        $template->class = implode(' ', array_unique($cssClasses));
    }

    /**
     * Generate the category data
     *
     * @param NewsCategory $category
     * @param Module       $module
     *
     * @return array
     */
    private function generateCategoryData(NewsCategory $category, Module $module)
    {
        $data = $category->getModel()->row();

        $data['instance'] = $category;
        $data['name'] = $category->getTitle();
        $data['class'] = $category->getCssClass();
        $data['linkTitle'] = StringUtil::specialchars($data['name']);
        $data['href'] = '';
        $data['hrefWithParam'] = '';
        $data['targetPage'] = null;

        // Overwrite the category links with filter page set in module
        if ($module->news_categoryFilterPage && ($targetPage = PageModel::findPublishedById($module->news_categoryFilterPage)) !== null) {
            $data['href'] = $this->urlGenerator->generateUrl($category, $targetPage);
            $data['hrefWithParam'] = $data['href'];
            $data['targetPage'] = $targetPage;
        } elseif (($targetPage = $category->getTargetPage()) !== null) {
            // Add the category target page and URLs
            $data['href'] = $targetPage->getFrontendUrl();
            $data['hrefWithParam'] = $this->urlGenerator->generateUrl($category, $targetPage);
            $data['targetPage'] = $targetPage;
        }

        // Register a function to generate category URL manually
        $data['generateUrl'] = function(PageModel $page, $absolute = false) use ($category) {
            return $this->urlGenerator->generateUrl($category, $page, $absolute);
        };

        // Add the image
        if (($image = $category->getImage()) !== null) {
            /** @var Controller $controllerAdapter */
            $controllerAdapter = $this->framework->getAdapter(Controller::class);
            $data['image'] = new \stdClass();
            $controllerAdapter->addImageToTemplate($data['image'], ['singleSRC' => $image->path, 'size' => $module->news_categoryImgSize]);
        } else {
            $data['image'] = null;
        }

        return $data;
    }
}
