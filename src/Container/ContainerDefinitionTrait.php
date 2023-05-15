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

use DI\Definition\Definition;
use DI\Definition\Exception\InvalidDefinition;
use DI\Definition\Source\MutableDefinitionSource;

/**
 * Default implementation of the method `extractDefinition` defined in the `ContainerInterface` of this namespace, to
 * extract the DI's Definition object, from PHPDI Builder, used to create the factory will be injected into
 * Symfony's container
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait ContainerDefinitionTrait
{
    private ?MutableDefinitionSource $originalDefinitions = null;

    public function extractDefinition(string $name): ?Definition
    {
        if (null === $this->originalDefinitions) {
            return null;
        }

        try {
            return $this->originalDefinitions->getDefinition($name);
        } catch (InvalidDefinition) {
            return null;
        }
    }
}
