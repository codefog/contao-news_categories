<?php

/**
 * news_categories extension for Contao Open Source CMS
 *
 * Copyright (C) 2011-2014 Codefog
 *
 * @package news_categories
 * @author  Webcontext <http://webcontext.com>
 * @author  Codefog <info@codefog.pl>
 * @author  Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 * @license LGPL
 */

namespace NewsCategories;

use Terminal42\ChangeLanguage\Event\ChangelanguageNavigationEvent;

class NewsCategoriesChangeLanguageListener
{
    /**
     * On change language navigation
     *
     * @param ChangelanguageNavigationEvent $event
     */
    public function onChangelanguageNavigation(ChangelanguageNavigationEvent $event)
    {
        $currentParam = NewsCategories::getParameterName();
        $newParam = NewsCategories::getParameterName($event->getNavigationItem()->getRootPage()->id);

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
