<?php

declare(strict_types=1);

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\EventListener;

use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Codefog\NewsCategoriesBundle\NewsCategoriesManager;
use Contao\Controller;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\FrontendTemplate;
use Contao\Model\Collection;
use Contao\Module;
use Contao\PageModel;
use Contao\StringUtil;

class TemplateListener implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    private array $urlCache = [];

    /**
     * TemplateListener constructor.
     */
    public function __construct(private readonly NewsCategoriesManager $manager, private readonly Studio $studio)
    {
    }

    /**
     * On parse the articles.
     */
    public function onParseArticles(FrontendTemplate $template, array $data, Module $module): void
    {
        /** @var NewsCategoryModel $newsCategoryModelAdapter */
        $newsCategoryModelAdapter = $this->framework->getAdapter(NewsCategoryModel::class);

        if (null === ($models = $newsCategoryModelAdapter->findPublishedByNews($data['id']))) {
            $template->categories = [];
            $template->categoriesList = [];

            return;
        }

        $this->addCategoriesToTemplate($template, $module, $models);
    }

    /**
     * Add categories to the template.
     */
    private function addCategoriesToTemplate(FrontendTemplate $template, Module $module, Collection $categories): void
    {
        $data = [];
        $list = [];
        $cssClasses = StringUtil::trimsplit(' ', $template->class);

        /** @var NewsCategoryModel $category */
        foreach ($categories as $category) {
            // Skip the categories not eligible for the current module
            if (!$this->manager->isVisibleForModule($category, $module)) {
                continue;
            }

            // Add category to data and list
            $data[$category->id] = $this->generateCategoryData($category, $module);
            $list[$category->id] = $category->getTitle();

            // Add the category CSS classes to news class
            $cssClasses = array_merge($cssClasses, StringUtil::trimsplit(' ', $category->getCssClass()));
        }

        // Sort the categories data alphabetically
        uasort($data, static fn ($a, $b) => strnatcasecmp((string) $a['name'], (string) $b['name']));

        // Sort the category list alphabetically
        asort($list);

        $template->categories = $data;
        $template->categoriesList = $list;

        if (\count($cssClasses = array_unique($cssClasses)) > 0) {
            $template->class = ' '.implode(' ', $cssClasses);
        }
    }

    /**
     * Generate the category data.
     *
     * @return array
     */
    private function generateCategoryData(NewsCategoryModel $category, Module $module)
    {
        $data = $category->row();

        $data['model'] = $category;
        $data['name'] = $category->getTitle();
        $data['class'] = $category->getCssClass();
        $data['href'] = '';
        $data['hrefWithParam'] = '';
        $data['targetPage'] = null;

        /** @var StringUtil $stringUtilAdapter */
        $stringUtilAdapter = $this->framework->getAdapter(StringUtil::class);
        $data['linkTitle'] = $stringUtilAdapter->specialchars($data['name']);

        /** @var PageModel $pageAdapter */
        $pageAdapter = $this->framework->getAdapter(PageModel::class);

        // Overwrite the category links with filter page set in module
        if ($module->news_categoryFilterPage && null !== ($targetPage = $pageAdapter->findPublishedById($module->news_categoryFilterPage))) {
            $data['href'] = $this->manager->generateUrl($category, $targetPage);
            $data['hrefWithParam'] = $data['href'];
            $data['targetPage'] = $targetPage;
        } elseif (null !== ($targetPage = $this->manager->getTargetPage($category))) {
            // Add the category target page and URLs
            $data['hrefWithParam'] = $this->manager->generateUrl($category, $targetPage);
            $data['targetPage'] = $targetPage;

            // Cache URL for better performance
            if (!isset($this->urlCache[$targetPage->id])) {
                $this->urlCache[$targetPage->id] = $targetPage->getFrontendUrl();
            }

            $data['href'] = $this->urlCache[$targetPage->id];
        }

        // Register a function to generate category URL manually
        $data['generateUrl'] = fn (PageModel $page, $absolute = false) => $this->manager->generateUrl($category, $page, $absolute);

        // Add the image
        if (null !== ($image = $this->manager->getImage($category))) {
            $data['image'] = $this
                ->studio
                ->createFigureBuilder()
                ->fromFilesModel($image)
                ->setSize($module->news_categoryImgSize)
                ->build()
                ->getLegacyTemplateData()
            ;
        } else {
            $data['image'] = null;
        }

        return $data;
    }
}
