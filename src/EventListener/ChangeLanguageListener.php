<?php

namespace Codefog\NewsCategoriesBundle\EventListener;

use Terminal42\ChangeLanguage\Event\ChangelanguageNavigationEvent;

class ChangeLanguageListener
{
    /**
     * On change language navigation
     *
     * @param ChangelanguageNavigationEvent $event
     */
    public function onChangelanguageNavigation(ChangelanguageNavigationEvent $event)
    {
        $currentParam = \NewsCategories\NewsCategories::getParameterName();
        $newParam = \NewsCategories\NewsCategories::getParameterName($event->getNavigationItem()->getRootPage()->id);

        if ($currentParam === $newParam) {
            return;
        }

        $parameters = $event->getUrlParameterBag();
        $attributes = $parameters->getUrlAttributes();

        $attributes[$newParam] = $attributes[$currentParam];
        unset($attributes[$currentParam]);

        $parameters->setUrlAttributes($attributes);
    }
}
