<?php

/*
 * DI Symfony Bridge.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        https://teknoo.software/libraries/php-di-symfony-bridge Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\DI\SymfonyBridge\DependencyInjection;

use DI\ContainerBuilder as DIContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Teknoo\DI\SymfonyBridge\Container\BridgeBuilder;
use Teknoo\DI\SymfonyBridge\Container\BridgeBuilderInterface;
use Teknoo\DI\SymfonyBridge\Container\Container;
use Teknoo\DI\SymfonyBridge\Extension\ExtensionInterface as BridgeExtensionInterface;
use Teknoo\DI\SymfonyBridge\Extension\InvalidExtensionException;

use function class_exists;
use function is_a;
use function is_array;
use function is_numeric;

/**
 * Symfony Bundle extension to parse configuration defined in `Configuration` and initialize the Bridge Builder with
 * the loaded configuration, and a new PHPDI Container builder and the current instance of Symfony Container to
 * inject all PHP-DI's entries into Symfony's Container.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class DIBridgeExtension extends Extension implements PrependExtensionInterface
{
    /**
     * Extensions declared by a Symfony service id, fetched from the application's container during `prepend()`.
     * (During `load()`, Symfony passes a temporary empty container where application's services are not available).
     *
     * @var array<string, object>
     */
    private array $preloadedExtensions = [];

    /**
     * @param class-string<BridgeBuilder> $bridgeBuilderClass
     */
    public function __construct(
        private readonly string $bridgeBuilderClass = BridgeBuilder::class,
    ) {
    }

    /**
     * @param array<string, mixed> $configuration
     * @return iterable<string>
     */
    private function extractExtensionsNames(array $configuration): iterable
    {
        if (empty($configuration['extensions']) || !is_array($configuration['extensions'])) {
            return;
        }

        foreach ($configuration['extensions'] as &$extensionConfiguration) {
            if (is_string($extensionConfiguration) && '' !== $extensionConfiguration) {
                yield $extensionConfiguration;

                continue;
            }

            if (
                is_array($extensionConfiguration)
                && !empty($extensionConfiguration['name'])
                && is_string($extensionConfiguration['name'])
            ) {
                yield $extensionConfiguration['name'];
            }
        }
    }

    /**
     * Extensions declared by a Symfony's service id must be fetched from the application's container, before Symfony
     * calls `load()` with a temporary container.
     */
    public function prepend(SymfonyContainerBuilder $container): void
    {
        /** @var array<int, array<string, mixed>> $configurations */
        $configurations = $container->getExtensionConfig($this->getAlias());
        foreach ($configurations as &$configuration) {
            foreach ($this->extractExtensionsNames($configuration) as $name) {
                if (isset($this->preloadedExtensions[$name]) || !$container->has($name)) {
                    continue;
                }

                $this->preloadedExtensions[$name] = $container->get($name);
            }
        }
    }

    private function configureExtension(object $extension, BridgeBuilderInterface $builder): void
    {
        if (!$extension instanceof BridgeExtensionInterface) {
            throw new InvalidExtensionException(
                $extension::class . ' is not an implementation of ' . BridgeExtensionInterface::class
            );
        }

        $extension->configure($builder);
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private function configurePHPDI(array &$configuration, BridgeBuilderInterface $builder): void
    {
        if (!empty($configuration['compilation_path']) && is_string($configuration['compilation_path'])) {
            $builder->prepareCompilation(
                $configuration['compilation_path']
            );
        }

        if (isset($configuration['enable_cache'])) {
            $builder->enableCache(
                !empty($configuration['enable_cache'])
            );
        }
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private function processDefinitions(array &$configuration, BridgeBuilderInterface $builder): void
    {
        if (empty($configuration['definitions']) || !is_array($configuration['definitions'])) {
            return;
        }

        /** @var array<int, array{priority?: int, file: string}> $definitions */
        $definitions = $configuration['definitions'];
        $builder->loadDefinition($definitions);
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private function processImport(array &$configuration, BridgeBuilderInterface $builder): void
    {
        if (empty($configuration['import']) || !is_array($configuration['import'])) {
            return;
        }

        foreach ($configuration['import'] as $diKey => $sfKey) {
            if (is_string($diKey) && is_string($sfKey)) {
                $builder->import($diKey, $sfKey);
            }
        }
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private function processExtensions(
        SymfonyContainerBuilder $container,
        array &$configuration,
        BridgeBuilderInterface $builder
    ): void {
        if (empty($configuration['extensions']) || !is_array($configuration['extensions'])) {
            return;
        }

        $toOrder = [];
        foreach ($configuration['extensions'] as $extensionConfiguration) {
            if (
                is_array($extensionConfiguration)
                && (!empty($extensionConfiguration['name']) && is_string($extensionConfiguration['name']))
                && (!isset($extensionConfiguration['priority']) || is_numeric($extensionConfiguration['priority']))
            ) {
                $toOrder[(int) ($extensionConfiguration['priority'] ?? 0)][] = $extensionConfiguration['name'];
            }
        }

        unset($extensionConfiguration);
        krsort($toOrder);

        foreach ($toOrder as &$namesList) {
            foreach ($namesList as $name) {
                if (isset($this->preloadedExtensions[$name])) {
                    $this->configureExtension($this->preloadedExtensions[$name], $builder);

                    continue;
                }

                if ($container->has($name)) {
                    $this->configureExtension($container->get($name), $builder);

                    continue;
                }

                if (
                    class_exists($name, true)
                    && is_a($name, BridgeExtensionInterface::class, true)
                ) {
                    $extension = $name::create();

                    $extension->configure($builder);

                    continue;
                }

                throw new InvalidExtensionException(
                    $name . ' does not exist or is not an implementation of ' . BridgeExtensionInterface::class
                );
            }
        }
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private function initializePHPDI(array &$configuration, SymfonyContainerBuilder $container): void
    {
        $bridgeBuilderClass = $this->bridgeBuilderClass;
        $builder = new $bridgeBuilderClass(
            new DIContainerBuilder(Container::class),
            $container
        );

        $this->configurePHPDI($configuration, $builder);

        $this->processDefinitions($configuration, $builder);

        $this->processImport($configuration, $builder);

        $this->processExtensions($container, $configuration, $builder);

        $builder->initializeSymfonyContainer();
    }

    /**
     * @param array<string, string|array<string|array<string>>> $configs
     */
    public function load(array $configs, SymfonyContainerBuilder $container): void
    {
        $configuration = new Configuration();
        /** @var array<string, mixed> $configs */
        $configs = $this->processConfiguration($configuration, $configs);

        if (!empty($configs)) {
            $this->initializePHPDI($configs, $container);
        }
    }
}
