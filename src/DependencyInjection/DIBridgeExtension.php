<?php

/*
 * DI Symfony Bridge.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.software/bridge-phpdi-symfony Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\DI\SymfonyBridge\DependencyInjection;

use DI\ContainerBuilder as DIContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Teknoo\DI\SymfonyBridge\Container\BridgeBuilder;
use Teknoo\DI\SymfonyBridge\Container\Container;

use function is_string;
use function is_scalar;
use function is_array;
use function is_iterable;

/**
 * Symfony Bundle extension to parse configuration defined in `Configuration` and initialize the Bridge Builder with
 * the loaded configuration, and a new PHPDI Container builder and the current instance of Symfony Container to
 * inject all PHP-DI's entries into Symfony's Container.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class DIBridgeExtension extends Extension
{
    /**
     * @param class-string<BridgeBuilder> $bridgeBuilderClass
     */
    public function __construct(
        private readonly string $bridgeBuilderClass = BridgeBuilder::class,
    ) {
    }

    /**
     * @param array<string, string|array<string>> $configuration
     */
    private function initializePHPDI(array $configuration, SymfonyContainerBuilder $container): void
    {
        $bridgeBuilderClass = $this->bridgeBuilderClass;
        $builder = new $bridgeBuilderClass(
            new DIContainerBuilder(Container::class),
            $container
        );

        if (!empty($configuration['compilation_path']) && is_string($configuration['compilation_path'])) {
            $builder->prepareCompilation(
                $configuration['compilation_path']
            );
        }

        if (isset($configuration['enable_cache'])  && is_scalar($configuration['enable_cache'])) {
            $builder->enableCache(
                !empty($configuration['enable_cache'])
            );
        }

        if (!empty($configuration['definitions']) && is_array($configuration['definitions'])) {
            $builder->loadDefinition(
                $configuration['definitions']
            );
        }

        if (!empty($configuration['import']) && is_iterable($configuration['import'])) {
            foreach ($configuration['import'] as $diKey => $sfKey) {
                $builder->import($diKey, $sfKey);
            }
        }

        $builder->initializeSymfonyContainer();
    }

    /**
     * @param array<string, string|array<string>> $configs
     */
    public function load(array $configs, SymfonyContainerBuilder $container): self
    {
        $configuration = new Configuration();
        $configs = $this->processConfiguration($configuration, $configs);

        if (!empty($configs)) {
            $this->initializePHPDI($configs, $container);
        }

        return $this;
    }
}
