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
use DI\Definition\ArrayDefinition;
use DI\Definition\FactoryDefinition;
use DI\Definition\ObjectDefinition;
use DI\Definition\ValueDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder as SfContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class BridgeBuilder
{
    use BridgeTrait;

    private DIContainerBuilder $diBuilder;

    private SfContainerBuilder $sfBuilder;

    private array $definitionsFiles = [];

    private array $definitionsImport = [];

    public function __construct(DIContainerBuilder $diBuilder, SfContainerBuilder $sfBuilder)
    {
        $this->diBuilder = $diBuilder;
        $this->sfBuilder = $sfBuilder;
    }

    public function loadDefinition(array $definitions): self
    {
        foreach ($definitions as &$definition) {
            $this->definitionsFiles[$definition] = true;
        }

        return $this;
    }

    public function import(string $diKey, string $sfKey): self
    {
        $this->definitionsImport[$diKey] = $sfKey;

        return $this;
    }

    private function getDIContainer(): Container
    {
        return $this->buildContainer(
            $this->diBuilder,
            $this->sfBuilder,
            \array_keys($this->definitionsFiles),
            $this->definitionsImport
        );
    }

    private function getBridgeDefinition(): Definition
    {
        return new Definition(
            Bridge::class,
            [
                new Reference(DIContainerBuilder::class),
                new Reference('service_container'),
                \array_keys($this->definitionsFiles),
                $this->definitionsImport
            ]
        );
    }

    public function initializeSymfonyContainer(): self
    {
        $diContainer = $this->getDIContainer();

        $definitions = [
            DIContainerBuilder::class => new Definition(DIContainerBuilder::class),
            Bridge::class => $this->getBridgeDefinition(),
        ];

        foreach ($diContainer->getKnownEntryNames() as $entryName) {
            $className = $entryName;
            if (!\class_exists($entryName) && !\interface_exists($entryName)) {
                $diDefinition = $diContainer->extractDefinition($entryName);

                if ($diDefinition instanceof ObjectDefinition) {
                    $className = $diDefinition->getClassName();
                } elseif ($diDefinition instanceof FactoryDefinition) {
                    $callable = $diDefinition->getCallable();

                    $rm = null;
                    if (\is_object($callable)) {
                        $ro = new \ReflectionObject($callable);
                        $rm = $ro->getMethod('__invoke');
                    } elseif(\is_array($callable)) {
                        $ro = new \ReflectionObject($callable[0]);
                        $rm = $ro->getMethod($callable[1]);
                    } else {
                        $rm = new \ReflectionFunction($callable);
                    }

                    $rt = $rm->getReturnType();
                    $className = $rt->p2;
                } elseif ($diDefinition instanceof ValueDefinition) {
                    $this->sfBuilder->setParameter($entryName, $diDefinition->getValue());

                    continue;
                } elseif ($diDefinition instanceof ArrayDefinition) {
                    $this->sfBuilder->setParameter($entryName, $diDefinition->getValues());

                    continue;
                }
            }

            $definition = new Definition($className);
            $definition->setFactory(new Reference(Bridge::class));
            $definition->setArguments([$entryName]);
            $definitions[$entryName] = $definition;
        }

        $this->sfBuilder->addDefinitions($definitions);

        return $this;
    }
}
