<?php

namespace Codefog\NewsCategoriesBundle;

use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Contao\Module;
use Contao\ModuleNewsArchive;
use Contao\ModuleNewsList;
use Contao\ModuleNewsReader;

class NewsCategory
{
    /**
     * @var NewsCategoryModel
     */
    private $model;

    /**
     * NewsCategory constructor.
     *
     * @param NewsCategoryModel $model
     */
    public function __construct(NewsCategoryModel $model)
    {
        $this->model = $model;
    }

    /**
     * @return NewsCategoryModel
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Get the title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->model->frontendTitle ?: $this->model->title;
    }

    /**
     * Get the CSS class
     *
     * @return string
     */
    public function getCssClass()
    {
        $cssClasses = [
            'news_category_' . $this->model->id,
            'category_' . $this->model->id,
        ];

        if ($this->model->cssClass) {
            $cssClasses[] = $this->model->cssClass;
        }

        return implode(' ', array_unique($cssClasses));
    }

    /**
     * Return true if the category is visible for module
     *
     * @param Module $module
     *
     * @return bool
     */
    public function isVisibleForModule(Module $module)
    {
        // List or archive module
        if ($this->model->hideInList && ($module instanceof ModuleNewsList || $module instanceof ModuleNewsArchive)) {
            return false;
        }

        // Reader module
        if ($this->model->hideInReader && $module instanceof ModuleNewsReader) {
            return false;
        }

        return true;
    }
}
