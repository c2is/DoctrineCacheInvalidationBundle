<?php

namespace C2is\DoctrineCacheInvalidationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 *
 * @author Nicolas Philippe <nikophil@gmail.com>
 */
class C2isDoctrineCacheInvalidationExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        if ($config['type'] == 'yml' && null === $config['yml_file']) {
            throw new \InvalidArgumentException("You must provide a valid YAML file.");
        }

        if ($config['type'] == 'annotation' && null !== $config['yml_file']) {
            throw new \InvalidArgumentException("yml_file parameter is not compatible with annotation type");
        }

        if (null !== $config['yml_file']) {
            $ymlFilePath = $container->getParameter('kernel.root_dir') . '/../' . $config['yml_file'];
            $fs = new Filesystem();
            if (!$fs->exists($ymlFilePath)) {
                throw new \InvalidArgumentException("You must provide a valid YAML file.");
            }
        }

        $container->setParameter('c2is_doctrine_cache_invalidator.type', $config['type']);
        $container->setAlias('c2is_doctrine_cache_invalidator.configuration_loader', sprintf('c2is_doctrine_cache_invalidator.configuration_loader.%s', $config['type']));

        $container->setParameter('c2is_doctrine_cache_invalidator.driver', $config['driver']);
        if ($config['driver'] != 'custom') {
            $container->setAlias('c2is_doctrine_cache_invalidator.cache_invalidator_driver', sprintf('c2is_doctrine_cache_invalidator.cache_invalidator_driver.%s', $config['driver']));
        } elseif (null !== $config['custom_driver_id']) {
            $container->setAlias('c2is_doctrine_cache_invalidator.cache_invalidator_driver', $config['custom_driver_id']);
        } else {
            throw new \InvalidArgumentException("You must provide the custom driver id.");
        }

        $container->setParameter('c2is_doctrine_cache_invalidator.doctrine_cache_driver_id', $config['doctrine_cache_driver_id']);
        $container->setAlias('c2is_doctrine_cache_invalidator.cache_provider_driver', sprintf('c2is_doctrine_cache_invalidator.cache_provider_driver.%s', $config['doctrine_cache_driver_id']));

        if (isset($config['cache_driver_options']) && null !== $config['cache_driver_options']) {
            $container->setParameter('c2is_doctrine_cache_invalidator.doctrine_cache_driver_options', $config['cache_driver_options']);
        } else {
            $container->setParameter('c2is_doctrine_cache_invalidator.doctrine_cache_driver_options', []);
        }

        $container->setParameter('c2is_doctrine_cache_invalidator.yml_file', $config['yml_file']);
    }
}
