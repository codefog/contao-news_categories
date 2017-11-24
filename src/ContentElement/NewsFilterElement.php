<?php

/*
 * News Categories Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\ContentElement;

use Contao\ContentModule;
use Contao\Module;
use Contao\ModuleModel;
use Contao\StringUtil;

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
        if (TL_MODE === 'FE'
            && !BE_USER_LOGGED_IN
            && ($this->invisible || ($this->start > 0 && $this->start > time()) || ($this->stop > 0 && $this->stop < time()))
        ) {
            return '';
        }

        // Return if the module could not be found
        if (null === ($moduleModel = ModuleModel::findByPk($this->news_module))) {
            return '';
        }

        $class = Module::findClass($moduleModel->type);

        // Return if the class does not exist
        if (!class_exists($class)) {
            return '';
        }

        $moduleModel->typePrefix = 'ce_';

        /** @var Module $module */
        $module = new $class($moduleModel, $this->strColumn);

        $this->mergeCssId($module);

        // Override news filter settings
        $module->news_filterCategories = $this->news_filterCategories;
        $module->news_relatedCategories = $this->news_relatedCategories;
        $module->news_includeSubcategories = $this->news_includeSubcategories;
        $module->news_filterDefault = $this->news_filterDefault;
        $module->news_filterPreserve = $this->news_filterPreserve;
        $module->news_categoryFilterPage = $this->news_categoryFilterPage;
        $module->news_categoryImgSize = $this->news_categoryImgSize;

        return $module->generate();
    }

    /**
     * Merge the CSS/ID stuff.
     *
     * @param Module $module
     */
    private function mergeCssId(Module $module)
    {
        $cssID = StringUtil::deserialize($module->cssID, true);

        // Override the CSS ID (see #305)
        if ($this->cssID[0]) {
            $cssID[0] = $this->cssID[0];
        }

        // Merge the CSS classes (see #6011)
        if ($this->cssID[1]) {
            $cssID[1] = trim($cssID[1].' '.$this->cssID[1]);
        }

        $module->cssID = $cssID;
    }
}
