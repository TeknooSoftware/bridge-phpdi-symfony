<?php

/*
 * Symfony Bridge.
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

namespace Teknoo\DI\SymfonyBridge\Container;

use Closure;
use DI\Container as DIContainer;
use DI\ContainerBuilder as DIContainerBuilder;
use DI\Definition\ArrayDefinition;
use DI\Definition\EnvironmentVariableDefinition;
use DI\Definition\Definition as DIDefinition;
use DI\Definition\FactoryDefinition;
use DI\Definition\ObjectDefinition;
use DI\Definition\Reference as DIReference;
use DI\Definition\StringDefinition;
use DI\Definition\ValueDefinition;
use InvalidArgumentException;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;
use Symfony\Component\DependencyInjection\ContainerBuilder as SfContainerBuilder;
use Symfony\Component\DependencyInjection\Definition as SfDefinition;
use Symfony\Component\DependencyInjection\Exception\RuntimeException as SfRuntimeException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference as SfReference;
use Teknoo\DI\SymfonyBridge\Container\Exception\InvalidContainerException;
use Traversable;
use UnitEnum;

use function array_keys;
use function class_exists;
use function count;
use function function_exists;
use function gettype;
use function implode;
use function interface_exists;
use function is_array;
use function is_object;
use function is_scalar;
use function is_string;
use function iterator_to_array;
use function krsort;
use function method_exists;
use function str_contains;

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
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class BridgeBuilder implements BridgeBuilderInterface
{
    use BridgeTrait;

    public const PREFIX_FOR_DEFAULT_ENV_VALUE = 'di_bridge_default_';

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

    /**
     * @param DIContainerBuilder<DIContainer> $diBuilder
     */
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

        unset($definitionFile);
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
            throw new InvalidContainerException(
                'PHP-DI Bridge : Error, invalid container type, need a ' . ContainerInterface::class
            );
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
        if (
            !is_array($value)
            && !is_scalar($value)
            && !$value instanceof UnitEnum
            && null !== $value
        ) {
            throw new InvalidArgumentException(
                "PHP-DI Bridge : Parameter $parameterName is not a scalar or an array, but " . gettype($value)
            );
        }

        $this->sfBuilder->setParameter(
            $parameterName,
            $value
        );
    }

    private function extractDIDefinition(ContainerInterface $container, string $entryName): DIDefinition
    {
        $diReference = null;
        $diDefinition = null;
        $visited = [];
        do {
            if ($diDefinition instanceof DIReference) {
                $entryName = $diDefinition->getTargetEntryName();
            }

            if (isset($visited[$entryName])) {
                throw new SfRuntimeException(
                    "PHP-DI Bridge : Circular reference detected for '$entryName' ("
                    . implode(' -> ', [...array_keys($visited), $entryName]) . ')'
                );
            }

            $visited[$entryName] = true;
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

        throw new ServiceNotFoundException("PHP-DI Bridge : Service $entryName is not available in PHP-DI Container");
    }

    /**
     * Returns the reflection of the callable used by a PHP-DI factory, for all callable forms supported by PHP-DI:
     * closures, invokable objects, [object, method], [class name, method] (static or resolved by PHP-DI at runtime),
     * 'Class::method' strings, function names and invokable class names.
     */
    private function getFactoryReflection(FactoryDefinition $definition): ReflectionFunctionAbstract
    {
        $definitionName = $definition->getName();
        $callable = $definition->getCallable();

        try {
            if ($callable instanceof Closure) {
                return new ReflectionFunction($callable);
            }

            if (is_object($callable)) {
                //Invokable object
                return new ReflectionMethod($callable, '__invoke');
            }

            if (
                is_array($callable)
                && 2 === count($callable)
                && is_string($callable[1])
                && (is_object($callable[0]) || (is_string($callable[0]) && class_exists($callable[0])))
            ) {
                //Public method from an object, or from a class name (static, or instantiated by PHP-DI at runtime)
                return new ReflectionMethod($callable[0], $callable[1]);
            }

            if (is_string($callable)) {
                if (str_contains($callable, '::')) {
                    return ReflectionMethod::createFromMethodName($callable);
                }

                if (function_exists($callable)) {
                    return new ReflectionFunction($callable);
                }

                if (class_exists($callable) && method_exists($callable, '__invoke')) {
                    //Invokable class, instantiated by PHP-DI at runtime
                    return new ReflectionMethod($callable, '__invoke');
                }
            }
        } catch (ReflectionException $error) {
            throw new SfRuntimeException(
                "PHP-DI Bridge : Invalid callable for '$definitionName' : " . $error->getMessage(),
                0,
                $error,
            );
        }

        throw new SfRuntimeException("PHP-DI Bridge : Callable not supported for '$definitionName'");
    }

    private function getClassFromFactory(FactoryDefinition $definition): string
    {
        $definitionName = $definition->getName();
        $reflection = $this->getFactoryReflection($definition);

        $returnType = $reflection->getReturnType();
        if (!$returnType instanceof ReflectionNamedType) {
            throw new SfRuntimeException(
                "PHP-DI Bridge : Missing a return type or non ReflectionNamedType from Reflection for '$definitionName'"
            );
        }

        $className = $returnType->getName();
        if ('self' !== $className && 'static' !== $className) {
            return $className;
        }

        //self and static return types are resolved from the declaring class, or from the closure's scope
        $scopeClass = match (true) {
            $reflection instanceof ReflectionMethod => $reflection->getDeclaringClass(),
            $reflection instanceof ReflectionFunction => $reflection->getClosureScopeClass(),
            default => null,
        };

        if (null === $scopeClass) {
            throw new SfRuntimeException(
                "PHP-DI Bridge : Unable to resolve the '$className' return type for '$definitionName'"
            );
        }

        return $scopeClass->getName();
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
            if ($diDefinition->isOptional()) {
                $defaultEntryName = self::PREFIX_FOR_DEFAULT_ENV_VALUE . $entryName;
                $this->setParameter($defaultEntryName, $diDefinition->getDefaultValue());
                $this->setParameter(
                    $entryName,
                    '%env(default:' . $defaultEntryName . ':' . $diDefinition->getVariableName() . ')%',
                );
            } else {
                $this->setParameter($entryName, '%env(' . $diDefinition->getVariableName() . ')%');
            }

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
