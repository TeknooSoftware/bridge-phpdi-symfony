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

use DI\Container as DIContainer;
use DI\Definition\Source\MutableDefinitionSource;
use DI\Proxy\ProxyFactory;
use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * Default implementation of the `ContainerInterface` with a non compiled PHP-DI instance.
 * Use `ContainerDefinitionTrait` to implement the method `extractDefinition` defined, to extract the DI's Definition
 * object, from PHPDI Builder, used to create the factory will be injected into Symfony's container.
 * The method `getKnownEntryNames` is already implemented by PHP-DI.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class Container extends DIContainer implements ContainerInterface
{
    use ContainerDefinitionTrait;

    public function __construct(
        ?MutableDefinitionSource $definitionSource = null,
        ?ProxyFactory $proxyFactory = null,
        ?PsrContainerInterface $wrapperContainer = null,
    ) {
        parent::__construct(
            $definitionSource,
            $proxyFactory,
            $wrapperContainer
        );

        $this->originalDefinitions = $definitionSource;
    }
}
