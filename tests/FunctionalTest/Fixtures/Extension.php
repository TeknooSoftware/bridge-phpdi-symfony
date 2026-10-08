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
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 *
 * @link        https://teknoo.software/libraries/php-di-symfony-bridge Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

namespace Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures;

use Teknoo\DI\SymfonyBridge\Container\BridgeBuilderInterface;
use Teknoo\DI\SymfonyBridge\Extension\ExtensionInterface;

class Extension implements ExtensionInterface
{
    public static int $configuredCounter = 0;

    public static function create(): ExtensionInterface
    {
        return new self();
    }

    public function configure(BridgeBuilderInterface $builder): ExtensionInterface
    {
        ++self::$configuredCounter;
        $builder->loadDefinition([['file' => __DIR__ . '/config/di_extension.php']]);

        return $this;
    }
}
