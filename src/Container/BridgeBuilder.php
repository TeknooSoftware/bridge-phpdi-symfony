<?php

/*
 * Symfony Bridge.
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
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/bridge-phpdi-symfony Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\DI\SymfonyBridge\Container;

use Closure;
use DI\ContainerBuilder as DIContainerBuilder;
use DI\Definition\ArrayDefinition;
use DI\Definition\EnvironmentVariableDefinition;
use DI\Definition\Definition as DIDefinition;
use DI\Definition\FactoryDefinition;
use DI\Definition\ObjectDefinition;
use DI\Definition\Reference as DIReference;
use DI\Definition\StringDefinition;
use DI\Definition\ValueDefinition;
use ReflectionFunction;
use ReflectionNamedType;
use ReflectionObject;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerBuilder as SfContainerBuilder;
use Symfony\Component\DependencyInjection\Definition as SfDefinition;
use Symfony\Component\DependencyInjection\Exception\RuntimeException as SfRuntimeException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference as SfReference;
use Teknoo\DI\SymfonyBridge\Container\Exception\InvalidContainerException;
use Traversable;

use function class_exists;
use function interface_exists;
use function is_array;
use function is_callable;
use function is_object;
use function is_string;
use function iterator_to_array;
use function krsort;

/**
 * Class used during the compilation of Symfony.
 * It will reuse a PHP DI Container builder to initialize a new DI Container with definitions files passed via
 *`loadDefinition`. (Compilation and Cache can be enabled via `prepareCompilation()` and `enableCache()`).
 * Symfony's entries can be imported via `import`.
 * After this, This builder will be browse all entries defined in the DI Container, to register them into Symfony
 * Container with Bridge container as factory (Needed arguments and returned type are conserved and also passed to
 * Symfony). PHP-DI's References and Factories are also managed
 * Parameters injected into PHP-DI as String, Values, Array and EnvVar are also imported into Symfony's Container
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/gd-text Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class BridgeBuilder
{
    use BridgeTrait;

    /**
     * @var array<string, array{priority?:int, file:string}>
     */
    private array $definitionsFiles = [];

    /**
     * @var array<string, string>
     */
    private array $definitionsImport = [];

    private ?string $compilationPath = null;

    private bool $cacheEnabled = false;

    public function __construct(
        private readonly DIContainerBuilder $diBuilder,
        private readonly SfContainerBuilder $sfBuilder,
    ) {
    }

    public function prepareCompilation(?string $compilationPath): self
    {
        $this->compilationPath = $compilationPath;

        return $this;
    }

    public function enableCache(bool $enable): self
    {
        $this->cacheEnabled = $enable;

        return $this;
    }

    /**
     * @param array<int, array{priority?:int, file:string}> $definitions
     */
    public function loadDefinition(array $definitions): self
    {
        foreach ($definitions as &$definition) {
            $this->definitionsFiles[$definition['file']] = $definition;
        }

        return $this;
    }

    public function import(string $diKey, string $sfKey): self
    {
        $this->definitionsImport[$diKey] = $sfKey;

        return $this;
    }

    /**
     * @return Traversable<string>
     */
    private function getOrderedDefinitionsFiles(): Traversable
    {
        $toOrder = [];
        foreach ($this->definitionsFiles as &$definitionFile) {
            $toOrder[(int) ($definitionFile['priority'] ?? 0)][] = $definitionFile['file'];
        }

        krsort($toOrder);

        //Can not use yield from with iterator_to_array, skip first entries
        foreach ($toOrder as &$list) {
            foreach ($list as &$file) {
                yield $file;
            }
        }
    }

    private function getDIContainer(): ContainerInterface
    {
        $container = $this->buildContainer(
            $this->diBuilder,
            $this->sfBuilder,
            $this->getOrderedDefinitionsFiles(),
            $this->definitionsImport,
            $this->compilationPath,
            $this->cacheEnabled
        );

        if (!$container instanceof ContainerInterface) {
            throw new InvalidContainerException('Error, invalid container type, need a ' . ContainerInterface::class);
        }

        return $container;
    }

    private function getBridgeDefinition(): SfDefinition
    {
        $definitionsFiles = iterator_to_array($this->getOrderedDefinitionsFiles());
        return new SfDefinition(
            Bridge::class,
            [
                new SfReference(DIContainerBuilder::class),
                new SfReference('service_container'),
                $definitionsFiles,
                $this->definitionsImport,
                $this->compilationPath,
                $this->cacheEnabled
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

    private function setParameter(string $parameterName, mixed $value): void
    {
        $this->sfBuilder->setParameter(
            $parameterName,
            $value
        );
    }

    private function extractDIDefinition(ContainerInterface $container, string $entryName): DIDefinition
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
        $definitionName = $definition->getName();
        $callable = $definition->getCallable();

        $reflectionMethod = null;
        if (!$callable instanceof Closure && is_object($callable) && is_callable($callable)) {
            //Invokable object
            $reflectionObject = new ReflectionObject($callable);
            $reflectionMethod = $reflectionObject->getMethod('__invoke');
        } elseif (is_array($callable) && is_callable($callable)) {
            //Callable is a public method from object
            $reflectionObject = new ReflectionObject($callable[0]);
            $reflectionMethod = $reflectionObject->getMethod($callable[1]);
        } elseif ($callable instanceof Closure || (is_string($callable) && is_callable($callable))) {
            //Is internal function or a closure
            $reflectionMethod = new ReflectionFunction($callable);
        } else {
            throw new SfRuntimeException("Callable not supported for '$definitionName'");
        }

        $returnType = $reflectionMethod->getReturnType();
        if (!$returnType instanceof ReflectionNamedType) {
            throw new SfRuntimeException(
                "Missing a return type or non \ReflectionNamedType from Reflection for '$definitionName'"
            );
        }

        return (string) $returnType->getName();
    }

    /**
     * @param array<int|string, mixed> $array
     * @return array<int|string, mixed>
     */
    private function convertArrayDefinition(array $array): array
    {
        $final = [];
        foreach ($array as $key => &$value) {
            if ($value instanceof ArrayDefinition) {
                $final[$key] = $this->convertArrayDefinition($value->getValues());
            } else {
                $final[$key] = $value;
            }
        }

        return $final;
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
            $this->setParameter($entryName, $this->convertArrayDefinition($diDefinition->getValues()));
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
            if (class_exists($entryName) || interface_exists($entryName)) {
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
