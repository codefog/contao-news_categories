<?php

declare(strict_types=1);

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\ContentElement;

use Contao\ContentModule;
use Contao\Controller;
use Contao\ModuleModel;
use Contao\StringUtil;
use Contao\System;

/**
 * @property int          $news_module
 * @property string|array $news_filterCategories
 * @property bool         $news_relatedCategories
 * @property bool         $news_includeSubcategories
 * @property string|array $news_filterDefault
 * @property bool         $news_filterPreserveefault
 * @property int          $news_categoryFilterPage
 * @property string|array $news_categoryImgSize
 */
class NewsFilterElement extends ContentModule
{
    /**
     * Parse the template.
     *
     * @return string
     */
    public function generate()
    {
        // Return if the element is not published
        if ($this->isHidden()) {
            return '';
        }

        // Return if the module could not be found
        if (null === ($moduleModel = ModuleModel::findByPk($this->news_module))) {
            return '';
        }

        // Clone the model, so we do not modify the shared model in the registry
        $objModel = $moduleModel->cloneOriginal();

        $this->mergeCssId($objModel);

        if (!empty($this->headline) && !empty($this->hl)) {
            $objModel->hl = $this->hl;
            $objModel->headline = $this->headline;
        }

        // Override news filter settings
        $objModel->news_filterCategories = $this->news_filterCategories;
        $objModel->news_relatedCategories = $this->news_relatedCategories;
        $objModel->news_includeSubcategories = $this->news_includeSubcategories;
        $objModel->news_filterDefault = $this->news_filterDefault;
        $objModel->news_filterPreserve = $this->news_filterPreserve;
        $objModel->news_categoryFilterPage = $this->news_categoryFilterPage;
        $objModel->news_categoryImgSize = $this->news_categoryImgSize;

        // Make the original content element accessible from the module template
        $objModel->newsFilterElement = $this;

        // Tag the content element (see #2137)
        if (null !== $this->objModel) {
            System::getContainer()->get('contao.cache.entity_tags')?->tagWithModelInstance($this->objModel);
        }

        return Controller::getFrontendModule($objModel, $this->strColumn);
    }

    /**
     * Merge the CSS/ID stuff.
     */
    private function mergeCssId(ModuleModel $module): void
    {
        $cssID = StringUtil::deserialize($module->cssID, true);

        // Override the CSS ID (see #305)
        if (!empty($this->cssID[0])) {
            $cssID[0] = $this->cssID[0];
        }

        // Merge the CSS classes (see #6011)
        if (!empty($this->cssID[1])) {
            $cssID[1] = trim(($cssID[1] ?? '').' '.$this->cssID[1]);
        }

        $module->cssID = $cssID;
    }
}
