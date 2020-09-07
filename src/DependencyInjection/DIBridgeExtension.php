<?php

/*
 * DI Symfony Bridge.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
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
use Teknoo\DI\SymfonyBridge\Container\Bridge;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class DIBridgeExtension extends Extension
{
    private function initializePHPDI(array $configuration, SymfonyContainerBuilder $container): void
    {
        $builderClass = $configuration['builder_class'];
        if (!\class_exists($builderClass)) {
            throw new \RuntimeException("$builderClass was not found");
        }

        $bridge = new Bridge(
            new $builderClass,
            $container
        );

        if ($configuration['definitions']) {
            $bridge->loadDefinition(
                $configuration['definitions'],
                $container->getParameter('%kernel.project_dir%') . '/vendor'
            );
        }

        if ($configuration['import']) {
            foreach ($configuration['import'] as $diKey => $sfKey) {
                $bridge->import($diKey, $sfKey);
            }
        }

        $bridge->initializeSymfonyContainer();
    }

    public function load(array $configs, SymfonyContainerBuilder $container): self
    {
        $configuration = new Configuration();
        $this->processConfiguration($configuration, $configs);

        if (isset($configs['di_bridge'])) {
            $this->initializePHPDI($configs['di_bridge'], $container);
        }

        return $this;
    }
}
