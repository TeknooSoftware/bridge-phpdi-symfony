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
 * @link        http://teknoo.software/di-symfony-bridge Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
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
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
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
