<?php

/*
 * DI Symfony Bridge.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/di-symfony-bridge Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\DI\SymfonyBridge\DependencyInjection;

use DI\ContainerBuilder as DIContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Teknoo\DI\SymfonyBridge\Container\BridgeBuilder;
use Teknoo\DI\SymfonyBridge\Container\Container;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class DIBridgeExtension extends Extension
{
    private string $bridgeBuilderClass;

    public function __construct(string $bridgeBuilderClass = BridgeBuilder::class)
    {
        $this->bridgeBuilderClass = $bridgeBuilderClass;
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private function initializePHPDI(array $configuration, SymfonyContainerBuilder $container): void
    {
        $bridgeBuilderClass = $this->bridgeBuilderClass;
        $builder = new $bridgeBuilderClass(
            new DIContainerBuilder(Container::class),
            $container
        );

        if (!empty($configuration['compilation_path'])) {
            $builder->prepareCompilation(
                $configuration['compilation_path']
            );
        }

        if (isset($configuration['enable_cache'])) {
            $builder->enableCache(
                !empty($configuration['enable_cache'])
            );
        }

        if ($configuration['definitions']) {
            $builder->loadDefinition(
                $configuration['definitions']
            );
        }

        if ($configuration['import']) {
            foreach ($configuration['import'] as $diKey => $sfKey) {
                $builder->import($diKey, $sfKey);
            }
        }

        $builder->initializeSymfonyContainer();
    }

    /**
     * @param array<string, mixed> $configs
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
