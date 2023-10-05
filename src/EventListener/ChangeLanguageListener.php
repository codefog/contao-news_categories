<?php

declare(strict_types=1);

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\EventListener;

use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Codefog\NewsCategoriesBundle\NewsCategoriesManager;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Framework\ContaoFramework;
use Terminal42\ChangeLanguage\Event\ChangelanguageNavigationEvent;
use Terminal42\DcMultilingualBundle\Model\Multilingual;

#[AsHook('changelanguageNavigation')]
class ChangeLanguageListener
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly NewsCategoriesManager $manager,
    ) {
    }

    public function __invoke(ChangelanguageNavigationEvent $event): void
    {
        $this->updateAlias($event);
        $this->updateParameter($event);
    }

    /**
     * Update the category alias value.
     */
    private function updateAlias(ChangelanguageNavigationEvent $event): void
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
                $model->getAlias($event->getNavigationItem()->getRootPage()->rootLanguage),
            );
        }
    }

    /**
     * Update the parameter name.
     */
    private function updateParameter(ChangelanguageNavigationEvent $event): void
    {
        $currentParam = $this->manager->getParameterName();
        $newParam = $this->manager->getParameterName($event->getNavigationItem()->getRootPage()->id);

        $parameters = $event->getUrlParameterBag();
        $attributes = $parameters->getUrlAttributes();

        if (!isset($attributes[$currentParam])) {
            return;
        }

        // Only add or change category param if the fallback page is a direct fallback
        if ($event->getNavigationItem()->isDirectFallback()) {
            $attributes[$newParam] = $attributes[$currentParam];

            if ($newParam !== $currentParam) {
                unset($attributes[$currentParam]);
            }
        } else {
            // Remove category param completely
            unset($attributes[$currentParam]);
        }

        $parameters->setUrlAttributes($attributes);
    }
}
