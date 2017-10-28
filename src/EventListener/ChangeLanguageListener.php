<?php

namespace Codefog\NewsCategoriesBundle\EventListener;

use Codefog\NewsCategoriesBundle\UrlGenerator;
use Terminal42\ChangeLanguage\Event\ChangelanguageNavigationEvent;

class ChangeLanguageListener
{
    /**
     * @var UrlGenerator
     */
    private $urlGenerator;

    /**
     * ChangeLanguageListener constructor.
     *
     * @param UrlGenerator $urlGenerator
     */
    public function __construct(UrlGenerator $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * On change language navigation
     *
     * @param ChangelanguageNavigationEvent $event
     */
    public function onChangelanguageNavigation(ChangelanguageNavigationEvent $event)
    {
        $currentParam = $this->urlGenerator->getParameterName();
        $newParam = $this->urlGenerator->getParameterName($event->getNavigationItem()->getRootPage()->id);

        if ($currentParam === $newParam) {
            return;
        }

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
