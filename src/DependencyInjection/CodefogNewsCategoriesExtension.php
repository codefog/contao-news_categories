<?php

declare(strict_types=1);

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\DependencyInjection;

use Codefog\NewsCategoriesBundle\Migration\BooleanFieldsMigration;
use Contao\CoreBundle\Migration\AbstractMigration;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class CodefogNewsCategoriesExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('listener.yml');
        $loader->load('services.yml');

        // Remove migration service for Contao 4.4
        if (!class_exists(AbstractMigration::class)) {
            $container->removeDefinition(BooleanFieldsMigration::class);
        }
    }
}
