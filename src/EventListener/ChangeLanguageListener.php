<?php

/*
 * News Categories Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\EventListener;

use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Codefog\NewsCategoriesBundle\NewsCategoriesManager;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Terminal42\ChangeLanguage\Event\ChangelanguageNavigationEvent;
use Terminal42\DcMultilingualBundle\Model\Multilingual;

class ChangeLanguageListener implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    /**
     * @var NewsCategoriesManager
     */
    private $manager;

    /**
     * ChangeLanguageListener constructor.
     *
     * @param NewsCategoriesManager $manager
     */
    public function __construct(NewsCategoriesManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * On change language navigation.
     *
     * @param ChangelanguageNavigationEvent $event
     */
    public function onChangelanguageNavigation(ChangelanguageNavigationEvent $event)
    {
        $this->updateAlias($event);
        $this->updateParameter($event);
    }

    /**
     * Update the category alias value.
     *
     * @param ChangelanguageNavigationEvent $event
     */
    private function updateAlias(ChangelanguageNavigationEvent $event)
    {
        /** @var NewsCategoryModel $modelAdapter */
        $modelAdapter = $this->framework->getAdapter(NewsCategoryModel::class);

        $param = $this->manager->getParameterName();

        if (!($alias = $event->getUrlParameterBag()->getUrlAttribute($param))) {
            return;
        }

        $model = $modelAdapter->findPublishedByIdOrAlias($alias);

        // Set the alias only for multilingual models
        if (null !== $model && $model instanceof Multilingual) {
            $event->getUrlParameterBag()->setUrlAttribute(
                $param,
                $model->getAlias($event->getNavigationItem()->getRootPage()->rootLanguage)
            );
        }
    }

    /**
     * Update the parameter name.
     *
     * @param ChangelanguageNavigationEvent $event
     */
    private function updateParameter(ChangelanguageNavigationEvent $event)
    {
        $currentParam = $this->manager->getParameterName();
        $newParam = $this->manager->getParameterName($event->getNavigationItem()->getRootPage()->id);

        $parameters = $event->getUrlParameterBag();
        $attributes = $parameters->getUrlAttributes();

        if (!isset($attributes[$currentParam])) {
            return;
        }

        $attributes[$newParam] = $attributes[$currentParam];
        unset($attributes[$currentParam]);

        $parameters->setUrlAttributes($attributes);
    }
}
