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

declare(strict_types=1);

namespace Teknoo\Tests\DI\SymfonyBridge\FunctionalTest;

use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\Kernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractFunctionalTests extends TestCase
{
    protected static function clearCache()
    {
        // Clear the cache
        $fs = new Filesystem();
        $fs->remove(__DIR__ . '/Fixtures/var/cache/dev');
        $fs->remove(__DIR__ . '/Fixtures/var/cache/phpdi');
    }

    public static function tearDownAfterClass(): void
    {
        static::clearCache();
        parent::tearDownAfterClass();
    }

    public static function setUpBeforeClass(): void
    {
        static::clearCache();
        parent::setUpBeforeClass();
    }

    protected function createKernel($configFile)
    {
        // Clear the cache
        $fs = new Filesystem();
        $fs->remove(__DIR__ . '/Fixtures/var/cache/dev');
        $fs->remove(__DIR__ . '/Fixtures/var/cache/phpdi');

        $kernel = new Kernel($configFile);
        $kernel->boot();

        return $kernel;
    }
}
