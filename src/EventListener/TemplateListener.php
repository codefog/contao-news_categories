<?php

namespace Codefog\NewsCategoriesBundle\EventListener;

use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Codefog\NewsCategoriesBundle\NewsCategory;
use Codefog\NewsCategoriesBundle\NewsCategoryFactory;
use Codefog\NewsCategoriesBundle\UrlGenerator;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FrontendTemplate;
use Contao\Module;
use Contao\PageModel;
use Contao\StringUtil;
use Haste\Model\Model;

class TemplateListener
{
    /**
     * @var NewsCategoryFactory
     */
    private $factory;

    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var UrlGenerator
     */
    private $urlGenerator;

    /**
     * TemplateListener constructor.
     *
     * @param NewsCategoryFactory $factory
     * @param ContaoFrameworkInterface $framework
     * @param UrlGenerator $urlGenerator
     */
    public function __construct(
        NewsCategoryFactory $factory,
        ContaoFrameworkInterface $framework,
        UrlGenerator $urlGenerator
    ) {
        $this->factory = $factory;
        $this->framework = $framework;
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
        /** @var Model $modelAdapter */
        $modelAdapter = $this->framework->getAdapter(Model::class);
        $ids = array_unique($modelAdapter->getRelatedValues('tl_news', 'categories', $data['id']));

        if (count($ids) === 0) {
            return;
        }

        /** @var NewsCategoryModel $newsCategoryModelAdapter */
        $newsCategoryModelAdapter = $this->framework->getAdapter(NewsCategoryModel::class);

        if (($models = $newsCategoryModelAdapter->findPublishedByIds($ids)) === null) {
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

        /** @var NewsCategory $category */
        foreach ($categories as $category) {
            // Skip the categories not eligible for the current module
            if (!$category->isVisibleForModule($module)) {
                continue;
            }

            $model = $category->getModel();

            $data[$model->id] = $this->generateCategoryData($category);
            $list[$model->id] = $category->getTitle();
        }

        // Sort the categories data alphabetically
        uasort($data, function($a, $b) {
            return strnatcasecmp($a['name'], $b['name']);
        });

        // Sort the category list alphabetically
        asort($list);

        $template->categories = $data;
        $template->categoriesList = $list;
    }

    /**
     * Generate the category data
     *
     * @param NewsCategory $category
     *
     * @return array
     */
    private function generateCategoryData(NewsCategory $category)
    {
        $data = $category->getModel()->row();

        $data['instance'] = $category;
        $data['name'] = $category->getTitle();
        $data['class'] = $category->getCssClass();
        $data['linkTitle'] = StringUtil::specialchars($data['name']);
        $data['href'] = '';
        $data['hrefWithParam'] = '';
        $data['targetPage'] = null;

        // Add the target page and URLs
        if (($targetPage = $this->urlGenerator->getTargetPage($category)) !== null) {
            $data['href'] = $targetPage->getFrontendUrl();
            $data['hrefWithParam'] = $this->urlGenerator->generateUrl($category, $targetPage);
            $data['targetPage'] = $targetPage;
        }

        // Register a function to generate category URL manually
        $data['generateUrl'] = function(PageModel $page, $absolute = false) use ($category) {
            return $this->urlGenerator->generateUrl($category, $page, $absolute);
        };

        return $data;
    }
}
