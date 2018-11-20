<?php

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\Picker;

use Codefog\NewsCategoriesBundle\PermissionChecker;
use Contao\CoreBundle\Picker\AbstractPickerProvider;
use Contao\CoreBundle\Picker\DcaPickerProviderInterface;
use Contao\CoreBundle\Picker\PickerConfig;

class NewsCategoriesPickerProvider extends AbstractPickerProvider implements DcaPickerProviderInterface
{
    /**
     * @var PermissionChecker
     */
    private $permissionChecker;

    /**
     * @param PermissionChecker $permissionChecker
     */
    public function setPermissionChecker(PermissionChecker $permissionChecker)
    {
        $this->permissionChecker = $permissionChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function getDcaTable()
    {
        return 'tl_news_category';
    }

    /**
     * {@inheritdoc}
     */
    public function getDcaAttributes(PickerConfig $config)
    {
        $attributes = ['fieldType' => 'checkbox'];

        if ($fieldType = $config->getExtra('fieldType')) {
            $attributes['fieldType'] = $fieldType;
        }

        if ($this->supportsValue($config)) {
            $attributes['value'] = \array_map('intval', \explode(',', $config->getValue()));
        }

        if (\is_array($rootNodes = $config->getExtra('rootNodes'))) {
            $attributes['rootNodes'] = $rootNodes;
        }

        return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl(PickerConfig $config)
    {
        // Set the news categories root in session for further reference in onload_callback (see #137)
        if (\is_array($rootNodes = $config->getExtra('rootNodes'))) {
            $_SESSION['NEWS_CATEGORIES_ROOT'] = $rootNodes;
        } else {
            unset($_SESSION['NEWS_CATEGORIES_ROOT']);
        }

        return parent::getUrl($config);
    }

    /**
     * {@inheritdoc}
     */
    public function convertDcaValue(PickerConfig $config, $value)
    {
        return (int) $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'newsCategoriesPicker';
    }

    /**
     * {@inheritdoc}
     */
    public function supportsContext($context)
    {
        if ($this->permissionChecker === null) {
            return false;
        }

        return 'newsCategories' === $context && ($this->permissionChecker->canUserManageCategories() || $this->permissionChecker->canUserAssignCategories());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsValue(PickerConfig $config)
    {
        foreach (\explode(',', $config->getValue()) as $id) {
            if (!\is_numeric($id)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRouteParameters(PickerConfig $config = null)
    {
        return ['do' => 'news', 'table' => 'tl_news_category'];
    }
}
