<?php

namespace Bez\Behat\RestExtension;

use Behat\Behat\Extension\ExtensionInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * @author Bez Hermoso <bezalelhermoso@gmail.com>
 */
class Extension implements ExtensionInterface
{
    /**
     * Loads a specific configuration.
     *
     * @param array            $config    Extension configuration hash (from behat.yml)
     * @param ContainerBuilder $container ContainerBuilder instance
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/services'));
        $loader->load('main.yml');

        if ($config['client'] === 'guzzle') {
            $loader->load('guzzle.yml');
            $this->buildGuzzle($config, $container);
        }

        if ($config['client'] === 'symfony') {
            $loader->load('syfmony.yml');
        }
    }

    /**
     * Setups configuration for current extension.
     *
     * @param ArrayNodeDefinition $builder
     */
    public function getConfig(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('client')->defaultValue('guzzle')->end()
                ->arrayNode('guzzle')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('url')->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * Returns compiler passes used by this extension.
     *
     * @return array
     */
    public function getCompilerPasses()
    {
        // TODO: Implement getCompilerPasses() method.
    }

    /**
     * @param array            $config
     * @param ContainerBuilder $container
     *
     * @throws \InvalidArgumentException
     */
    private function buildGuzzle(array $config, ContainerBuilder $container)
    {
        if (empty($container['guzzle']['url'])) {
            throw new \InvalidArgumentException('"guzzle.url" must be set.');
        }

        $container->setParameter('behat.rest_extension.guzzle.url', $config['guzzle']['url']);
    }
}

return new Extension();