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
use DI\Definition\EnvironmentVariableDefinition;
use DI\Definition\Definition as DIDefinition;
use DI\Definition\FactoryDefinition;
use DI\Definition\ObjectDefinition;
use DI\Definition\Reference as DIReference;
use DI\Definition\StringDefinition;
use DI\Definition\ValueDefinition;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder as SfContainerBuilder;
use Symfony\Component\DependencyInjection\Definition as SfDefinition;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference as SfReference;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class BridgeBuilder
{
    use BridgeTrait;

    private DIContainerBuilder $diBuilder;

    private SfContainerBuilder $sfBuilder;

    /**
     * @var array<string, bool>
     */
    private array $definitionsFiles = [];

    /**
     * @var array<string, string>
     */
    private array $definitionsImport = [];

    public function __construct(DIContainerBuilder $diBuilder, SfContainerBuilder $sfBuilder)
    {
        $this->diBuilder = $diBuilder;
        $this->sfBuilder = $sfBuilder;
    }

    /**
     * @param array<int, string> $definitions
     * @return $this
     */
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
        $container = $this->buildContainer(
            $this->diBuilder,
            $this->sfBuilder,
            \array_keys($this->definitionsFiles),
            $this->definitionsImport
        );

        if (!$container instanceof Container) {
            throw new \RuntimeException('Error bad container needed');
        }

        return $container;
    }

    private function getBridgeDefinition(): SfDefinition
    {
        return new SfDefinition(
            Bridge::class,
            [
                new SfReference(DIContainerBuilder::class),
                new SfReference('service_container'),
                \array_keys($this->definitionsFiles),
                $this->definitionsImport
            ]
        );
    }

    private function createDefinition(
        string $className,
        string $diEntryName
    ): SfDefinition {
        $definition = new SfDefinition($className);
        $definition->setFactory(new SfReference(Bridge::class));
        $definition->setArguments([$diEntryName]);
        $definition->setPublic(true);

        return $definition;
    }

    /**
     * @param mixed $value
     */
    private function setParameter(string $parameterName, $value): void
    {
        $this->sfBuilder->setParameter(
            $parameterName,
            $value
        );
    }

    private function extractDIDefinition(Container $container, string $entryName): DIDefinition
    {
        $diReference = null;
        $diDefinition = null;
        do {
            if ($diDefinition instanceof DIReference) {
                $entryName = $diDefinition->getTargetEntryName();
            }

            $diDefinition = $container->extractDefinition($entryName);

            //Symfony container passed is not fully completed (tmp container), so if the reference was not found,
            //returns the last reference, it will be resolved at runing time
            if ($diDefinition instanceof DIReference) {
                $diReference = $diDefinition;
            }
        } while ($diDefinition instanceof DIReference);

        if ($diDefinition instanceof DIDefinition || $diReference instanceof DIReference) {
            return $diDefinition ?? $diReference;
        }

        throw new ServiceNotFoundException("Service $entryName is not available in PHP-DI Container");
    }

    private function getClassFromFactory(FactoryDefinition $definition): string
    {
        $callable = $definition->getCallable();

        $reflectionMethod = null;
        if (!$callable instanceof \Closure && \is_object($callable) && \is_callable($callable)) {
            //Invokable object
            $reflectionObject = new \ReflectionObject($callable);
            $reflectionMethod = $reflectionObject->getMethod('__invoke');
        } elseif (\is_array($callable) && \is_callable($callable)) {
            //Callable is a public method from object
            $reflectionObject = new \ReflectionObject($callable[0]);
            $reflectionMethod = $reflectionObject->getMethod($callable[1]);
        } elseif ($callable instanceof \Closure || (\is_string($callable) && \is_callable($callable))) {
            //Is internal function or a closure
            $reflectionMethod = new \ReflectionFunction($callable);
        } else {
            throw new RuntimeException('Callable not supported');
        }

        $returnType = $reflectionMethod->getReturnType();
        if (!$returnType instanceof \ReflectionNamedType) {
            throw new RuntimeException('This bridge supports only \ReflectionNamedType from Reflection');
        }

        return (string) $returnType->getName();
    }

    /**
     * @param array<string, SfDefinition> $definitions
     */
    private function convertDefinition(DIDefinition $diDefinition, string $entryName, array &$definitions): void
    {
        if ($diDefinition instanceof ObjectDefinition) {
            $definitions[$entryName] = $this->createDefinition($diDefinition->getClassName(), $entryName);

            return;
        }

        if ($diDefinition instanceof FactoryDefinition) {
            $definitions[$entryName] = $this->createDefinition(
                $this->getClassFromFactory($diDefinition),
                $entryName
            );

            return;
        }

        if ($diDefinition instanceof DIReference) {
            $alias = $this->sfBuilder->setAlias($entryName, $diDefinition->getTargetEntryName());
            $alias->setPublic(true);

            return;
        }

        if ($diDefinition instanceof EnvironmentVariableDefinition) {
            $this->setParameter($entryName, '%env(' . $diDefinition->getVariableName() . ')%');

            return;
        }

        if ($diDefinition instanceof StringDefinition) {
            $this->setParameter($entryName, $diDefinition->getExpression());

            return;
        }

        if ($diDefinition instanceof ValueDefinition) {
            $this->setParameter($entryName, $diDefinition->getValue());

            return;
        }

        if ($diDefinition instanceof ArrayDefinition) {
            $this->setParameter($entryName, $diDefinition->getValues());

            return;
        }
    }

    public function initializeSymfonyContainer(): self
    {
        $diContainer = $this->getDIContainer();

        $definitions = [
            DIContainerBuilder::class => new SfDefinition(DIContainerBuilder::class),
            Bridge::class => $this->getBridgeDefinition(),
        ];

        foreach ($diContainer->getKnownEntryNames() as $entryName) {
            if (\class_exists($entryName) || \interface_exists($entryName)) {
                $definitions[$entryName] = $this->createDefinition($entryName, $entryName);

                continue;
            }

            $diDefinition = $this->extractDIDefinition($diContainer, $entryName);

            $this->convertDefinition($diDefinition, $entryName, $definitions);
        }

        $this->sfBuilder->addDefinitions($definitions);

        return $this;
    }
}
