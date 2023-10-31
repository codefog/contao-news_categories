<?php

declare(strict_types=1);

namespace Codefog\NewsCategoriesBundle\FrontendModule;

use Codefog\HasteBundle\Model\DcaRelationsModel;
use Codefog\NewsCategoriesBundle\Criteria\NewsCriteriaBuilder;
use Codefog\NewsCategoriesBundle\Exception\NoNewsException;
use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Codefog\NewsCategoriesBundle\NewsCategoriesManager;
use Contao\BackendTemplate;
use Contao\Controller;
use Contao\CoreBundle\File\Metadata;
use Contao\Database;
use Contao\Input;
use Contao\Model\Collection;
use Contao\ModuleNews;
use Contao\NewsModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Template;

/**
 * @property string|array $news_archives
 * @property string       $news_order
 * @property string       $news_featured
 * @property bool         $news_showQuantity
 * @property string|array $news_categories
 * @property bool         $news_customCategories
 * @property string|array $news_filterCategories
 * @property bool         $news_filterCategoriesCumulative
 * @property bool         $news_filterCategoriesUnion
 * @property bool         $news_relatedCategories
 * @property string       $news_relatedCategoriesOrder
 * @property bool         $news_includeSubcategories
 * @property string|array $news_filterDefault
 * @property bool         $news_filterPreserve
 * @property bool         $news_resetCategories
 * @property bool         $news_showEmptyCategories
 * @property bool         $news_forceCategoryUrl
 * @property bool         $news_enableCanonicalUrls
 * @property string|array $news_categoriesRoot
 * @property int          $news_categoryFilterPage
 * @property string|array $news_categoryImgSize
 * @property Template     $Template
 */
abstract class NewsModule extends ModuleNews
{
    /**
     * Active category.
     */
    protected NewsCategoryModel|null $activeCategory = null;

    /**
     * Active categories.
     *
     * @var Collection<NewsCategoryModel>|null
     */
    protected Collection|null $activeCategories = null;

    /**
     * News categories of the current news item.
     */
    protected array $currentNewsCategories = [];

    protected NewsCategoriesManager $manager;

    protected PageModel|null $targetPage = null;

    /**
     * Display a wildcard in the back end.
     *
     * @return string
     */
    public function generate()
    {
        $container = System::getContainer();

        if (
            ($request = $container->get('request_stack')->getCurrentRequest())
            && $container->get('contao.routing.scope_matcher')->isBackendRequest($request)
        ) {
            $template = new BackendTemplate('be_wildcard');

            $template->wildcard = '### '.mb_strtoupper($GLOBALS['TL_LANG']['FMD'][$this->type][0] ?? '').' ###';
            $template->title = $this->headline;
            $template->id = $this->id;
            $template->link = $this->name;
            $template->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

            return $template->parse();
        }

        $this->news_archives = $this->sortOutProtected(StringUtil::deserialize($this->news_archives, true));

        // Return if there are no archives
        if (0 === \count($this->news_archives)) {
            return '';
        }

        $this->manager = System::getContainer()->get(NewsCategoriesManager::class);
        $this->currentNewsCategories = $this->getCurrentNewsCategories();

        return parent::generate();
    }

    /**
     * Get the URL category separator character.
     */
    public static function getCategorySeparator(): string
    {
        return '__';
    }

    /**
     * Get the categories.
     *
     * @return Collection<NewsCategoryModel>|null
     */
    protected function getCategories(): Collection|null
    {
        $customCategories = $this->news_customCategories ? StringUtil::deserialize($this->news_categories, true) : [];

        // Get the subcategories of custom categories
        if (!empty($customCategories)) {
            $customCategories = NewsCategoryModel::getAllSubcategoriesIds($customCategories);
        }

        // Get all categories whether they have news or not
        if ($this->news_showEmptyCategories) {
            if (!empty($customCategories)) {
                $categories = NewsCategoryModel::findPublishedByIds($customCategories);
            } else {
                $categories = NewsCategoryModel::findPublished();
            }
        } else {
            // Get the categories that do have news assigned
            $categories = NewsCategoryModel::findPublishedByArchives($this->news_archives, $customCategories);
        }

        return $categories;
    }

    /**
     * Get the active categories.
     *
     * @return Collection<NewsCategoryModel>|null
     */
    protected function getActiveCategories(array $customCategories = []): Collection|null
    {
        $param = System::getContainer()->get(NewsCategoriesManager::class)->getParameterName();

        if (!($aliases = Input::get($param))) {
            return null;
        }

        $aliases = StringUtil::trimsplit(static::getCategorySeparator(), $aliases);
        $aliases = array_unique(array_filter($aliases));

        if (0 === \count($aliases)) {
            return null;
        }

        // Get the categories that do have news assigned
        $models = NewsCategoryModel::findPublishedByArchives($this->news_archives, $customCategories, $aliases);

        // No models have been found but there are some aliases present
        if (null === $models && 0 !== \count($aliases)) {
            Controller::redirect($this->getTargetPage()->getFrontendUrl());
        }

        // Validate the provided aliases with the categories found
        if (null !== $models) {
            $realAliases = [];

            /** @var NewsCategoryModel $model */
            foreach ($models as $model) {
                $realAliases[] = $this->manager->getCategoryAlias($model, $GLOBALS['objPage']);
            }

            if (\count(array_diff($aliases, $realAliases)) > 0) {
                Controller::redirect($this->getTargetPage()->getFrontendUrl(sprintf(
                    '/%s/%s',
                    $this->manager->getParameterName($GLOBALS['objPage']->rootId),
                    implode(static::getCategorySeparator(), $realAliases),
                )));
            }
        }

        return $models;
    }

    /**
     * Get the inactive categories.
     *
     * @return Collection<NewsCategoryModel>|null
     */
    protected function getInactiveCategories(array $customCategories = []): Collection|null
    {
        $excludedIds = [];

        // Find only the categories that still can display some results combined with active categories
        if (null !== $this->activeCategories) {
            // Union filtering
            if ($this->news_filterCategoriesUnion) {
                $excludedIds = $this->activeCategories->fetchEach('id');
            } else {
                // Intersection filtering
                $columns = [];
                $values = [];

                // Collect the news that match all active categories
                /** @var NewsCategoryModel $activeCategory */
                foreach ($this->activeCategories as $activeCategory) {
                    try {
                        $criteria = System::getContainer()->get(NewsCriteriaBuilder::class)->create($this->news_archives);
                        $criteria->setCategory($activeCategory->id, false, (bool) $this->news_includeSubcategories);
                    } catch (NoNewsException) {
                        continue;
                    }

                    $columns[] = $criteria->getColumns();
                    $values = $criteria->getValues();
                }

                // Should not happen but you never know
                if (0 === \count($columns)) {
                    return null;
                }

                $newsIds = Database::getInstance()
                    ->prepare('SELECT id FROM tl_news WHERE '.implode(' AND ', array_merge(...$columns)))
                    ->execute(array_merge(...$values))
                    ->fetchEach('id')
                ;

                if (0 === \count($newsIds)) {
                    return null;
                }

                $categoryIds = DcaRelationsModel::getRelatedValues('tl_news', 'categories', $newsIds);
                $categoryIds = array_map('intval', $categoryIds);
                $categoryIds = array_unique(array_filter($categoryIds));

                // Include the parent categories
                if ($this->news_includeSubcategories) {
                    foreach ($categoryIds as $categoryId) {
                        $categoryIds = array_merge($categoryIds, array_map('intval', Database::getInstance()->getParentRecords($categoryId, 'tl_news_category')));
                    }
                }

                // Remove the active categories, so they are not considered again
                $categoryIds = array_diff($categoryIds, $this->activeCategories->fetchEach('id'));

                // Filter by custom categories
                if (\count($customCategories) > 0) {
                    $categoryIds = array_intersect($categoryIds, $customCategories);
                }

                $categoryIds = array_values(array_unique($categoryIds));

                if (0 === \count($categoryIds)) {
                    return null;
                }

                $customCategories = $categoryIds;
            }
        }

        return NewsCategoryModel::findPublishedByArchives($this->news_archives, $customCategories, [], $excludedIds);
    }

    /**
     * Get the target page.
     */
    protected function getTargetPage(): PageModel
    {
        if (null === $this->targetPage) {
            if (
                $this->jumpTo > 0
                && (int) $GLOBALS['objPage']->id !== (int) $this->jumpTo
                && null !== ($target = PageModel::findPublishedById($this->jumpTo))
            ) {
                $this->targetPage = $target;
            } else {
                $this->targetPage = $GLOBALS['objPage'];
            }
        }

        return $this->targetPage;
    }

    /**
     * Get the category IDs of the current news item.
     *
     * @return array<int>
     */
    protected function getCurrentNewsCategories(): array
    {
        if (
            !($alias = Input::get('auto_item', false, true))
            || null === ($news = NewsModel::findPublishedByParentAndIdOrAlias($alias, $this->news_archives))
        ) {
            return [];
        }

        $ids = DcaRelationsModel::getRelatedValues('tl_news', 'categories', $news->id);

        return array_map('intval', array_unique($ids));
    }

    /**
     * Generate the item.
     *
     * @param string $url
     * @param string $link
     * @param string $title
     * @param string $cssClass
     * @param bool   $isActive
     * @param string $subitems
     */
    protected function generateItem($url, $link, $title, $cssClass, $isActive, $subitems = '', NewsCategoryModel|null $category = null): array
    {
        $data = [];

        // Set the data from category
        if (null !== $category) {
            $data = $category->row();
        }

        $data['isActive'] = $isActive;
        $data['subitems'] = $subitems;
        $data['class'] = $cssClass;
        $data['title'] = StringUtil::specialchars($title);
        $data['linkTitle'] = StringUtil::specialchars($title);
        $data['link'] = $link;
        $data['href'] = StringUtil::ampersand($url);
        $data['quantity'] = 0;

        // Add the "active" class
        if ($isActive) {
            $data['class'] = trim($data['class'].' active');
        }

        // Add the "submenu" class
        if ($subitems) {
            $data['class'] = trim($data['class'].' submenu');
        }

        // Add the news quantity
        if ($this->news_showQuantity) {
            if (null === $category) {
                $data['quantity'] = NewsCategoryModel::getUsage($this->news_archives, null, false, [], (bool) $this->news_filterCategoriesUnion);
            } else {
                $data['quantity'] = NewsCategoryModel::getUsage(
                    $this->news_archives,
                    $category->id,
                    (bool) $this->news_includeSubcategories,
                    null !== $this->activeCategories ? $this->activeCategories->fetchEach('id') : [],
                    (bool) $this->news_filterCategoriesUnion,
                );
            }
        }

        // Add the image
        if (null !== $category && null !== ($image = $this->manager->getImage($category))) {
            $data['image'] = System::getContainer()
                ->get('contao.image.studio')
                ?->createFigureBuilder()
                ->fromFilesModel($image)
                ->setSize($this->news_categoryImgSize)
                ->setMetadata(new Metadata([
                    Metadata::VALUE_ALT => $title,
                    Metadata::VALUE_TITLE => $title,
                ]))
                ->build()
                ->getLegacyTemplateData()
            ;
        } else {
            $data['image'] = null;
        }

        return $data;
    }

    /**
     * Generate the item CSS class.
     */
    protected function generateItemCssClass(NewsCategoryModel $category): string
    {
        $cssClasses = [$category->getCssClass()];

        // Add the trail class
        if (\in_array((int) $category->id, $this->manager->getTrailIds($category), true)) {
            $cssClasses[] = 'trail';
        } elseif (null !== $this->activeCategory && \in_array((int) $category->id, $this->manager->getTrailIds($this->activeCategory), true)) {
            $cssClasses[] = 'trail';
        }

        // Add the news trail class
        if (\in_array((int) $category->id, $this->currentNewsCategories, true)) {
            $cssClasses[] = 'news_trail';
        }

        return implode(' ', $cssClasses);
    }
}
