<?php

/*
 * Symfony Bridge.
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

namespace Teknoo\DI\SymfonyBridge\Container;

use DI\ContainerBuilder as DIContainerBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder as SfContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use function DI\get;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Bridge
{
    private DIContainerBuilder $diBuilder;

    private SfContainerBuilder $sfBuilder;

    public function __construct(DIContainerBuilder $diBuilder, SfContainerBuilder $sfBuilder)
    {
        $this->diBuilder = $diBuilder;
        $this->sfBuilder = $sfBuilder;

        $this->wrapContainer();
    }

    private function wrapContainer(): void
    {
        $this->diBuilder->wrapContainer($this->sfBuilder);
    }

    public function loadDefinition(array $definitions, string $vendorPath): self
    {
        foreach ($definitions as &$definition) {
            $definition = \str_replace('%vendor%', $vendorPath, $definition);

            $this->diBuilder->addDefinitions($definition);
        }

        return $this;
    }

    public function import(string $diKey, string $sfKey): self
    {
        $this->diBuilder->addDefinitions([
            $diKey => static function (ContainerInterface $container) use ($sfKey) {
                return $container->get($sfKey);
            }
        ]);
    }

    public function initializeSymfonyContainer(): self
    {
        $diContainer = $this->diBuilder->build();

        $definitions = [];
        foreach ($diContainer->getKnownEntryNames() as $entryName) {
            $definition = new Definition($entryName);
            $definition->setFactory(static function () use ($diContainer, $entryName) {
                return $diContainer->get($entryName);
            });

            $definitions[$entryName] = $definition;
        }

        $this->sfBuilder->addDefinitions($definitions);
    }
}
