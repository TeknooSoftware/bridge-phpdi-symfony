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
 * @link        https://teknoo.software/libraries/php-di-symfony-bridge Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\DI\SymfonyBridge\Container;

use DI\Definition\Definition;
use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * Extension of PSR Container Interface, dedicated to PHP-DI, to add to usefull methods for the bridge :
 *  - `getKnownEntryNames()` to list all DI's entries defined in this container
 *  - `extractDefinition` to extract the DI's Definition object, used to create the factory will be injected into
 *    Symfony's container
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
interface ContainerInterface extends PsrContainerInterface
{
    public function extractDefinition(string $name): ?Definition;

    /**
     * Get defined container entries.
     *
     * @return string[]
     */
    public function getKnownEntryNames(): array;
}
