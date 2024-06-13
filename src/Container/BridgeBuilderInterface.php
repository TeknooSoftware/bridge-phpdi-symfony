<?php

/*
 * Symfony Bridge.
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
use ReflectionFunction;
use ReflectionNamedType;
use ReflectionObject;
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
 * Interface defined builder used during the compilation of Symfony.
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
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
interface BridgeBuilderInterface
{
    public const PREFIX_FOR_DEFAULT_ENV_VALUE = 'di_bridge_default_';

    /**
     * @param DIContainerBuilder<DIContainer> $diBuilder
     */
    public function __construct(
        DIContainerBuilder $diBuilder,
        SfContainerBuilder $sfBuilder,
    );

    public function prepareCompilation(?string $compilationPath): self;

    public function enableCache(bool $enable): self;

    /**
     * @param array<int, array{priority?:int, file:string}> $definitions
     */
    public function loadDefinition(array $definitions): self;

    public function import(string $diKey, string $sfKey): self;

    public function initializeSymfonyContainer(): self;
}
