<?php

namespace Codefog\NewsCategoriesBundle\EventListener;

use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Codefog\NewsCategoriesBundle\UrlGenerator;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Terminal42\ChangeLanguage\Event\ChangelanguageNavigationEvent;
use Terminal42\DcMultilingualBundle\Model\Multilingual;

class ChangeLanguageListener implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

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
        $this->updateAlias($event);
        $this->updateParameter($event);
    }

    /**
     * Update the category alias value
     *
     * @param ChangelanguageNavigationEvent $event
     */
    private function updateAlias(ChangelanguageNavigationEvent $event)
    {
        /** @var NewsCategoryModel $modelAdapter */
        $modelAdapter = $this->framework->getAdapter(NewsCategoryModel::class);

        $param = $this->urlGenerator->getParameterName();

        if (!($alias = $event->getUrlParameterBag()->getUrlAttribute($param))) {
            return;
        }

        $model = $modelAdapter->findPublishedByIdOrAlias($alias);

        // Set the alias only for multilingual models
        if ($model !== null && $model instanceof Multilingual) {
            $event->getUrlParameterBag()->setUrlAttribute(
                $param,
                $model->getAlias($event->getNavigationItem()->getRootPage()->rootLanguage)
            );
        }
    }

    /**
     * Update the parameter name
     *
     * @param ChangelanguageNavigationEvent $event
     */
    private function updateParameter(ChangelanguageNavigationEvent $event)
    {
        $currentParam = $this->urlGenerator->getParameterName();
        $newParam = $this->urlGenerator->getParameterName($event->getNavigationItem()->getRootPage()->id);

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
