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

use DI\Definition\Source\SourceCache;
use PHPUnit\Framework\Attributes\CoversNothing;
use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\Class2;
use Psr\Container\ContainerInterface;
use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\ContainerAwareController;

#[CoversNothing]
class KernelTests extends AbstractFunctionalTests
{
    public function testKernelShouldBoot(): void
    {
        $kernel = $this->createKernel('empty.yml');

        $this->assertInstanceOf(ContainerInterface::class, $kernel->getContainer());
    }

    public function testKernelShouldBootWithCache(): void
    {
        if (!SourceCache::isSupported()) {
            self::markTestSkipped('APCu is not enabled');
        }

        $kernel = $this->createKernel('empty_with_cache.yml');

        $this->assertInstanceOf(ContainerInterface::class, $kernel->getContainer());
    }

    public function testKernelShouldBootWithCompilation(): void
    {
        $kernel = $this->createKernel('empty_with_compilation.yml');

        $this->assertInstanceOf(ContainerInterface::class, $kernel->getContainer());
    }

    public function testSymfonyShouldResolveClassesFromYaml(): void
    {
        $kernel = $this->createKernel('class2.yml');

        $object = $kernel->getContainer()->get('class2');
        $this->assertInstanceOf(Class2::class, $object);
    }

    public function testSymfonyShouldResolveClassesFromYamlWithCache(): void
    {
        if (!SourceCache::isSupported()) {
            self::markTestSkipped('APCu is not enabled');
        }

        $kernel = $this->createKernel('class2_with_cache.yml');

        $object = $kernel->getContainer()->get('class2');
        $this->assertInstanceOf(Class2::class, $object);
    }

    public function testSymfonyShouldResolveClassesFromYamlWithCompilation(): void
    {
        $kernel = $this->createKernel('class2_with_compilation.yml');

        $object = $kernel->getContainer()->get('class2');
        $this->assertInstanceOf(Class2::class, $object);
    }

    public function testSymfonyShouldResolveClassesFromDI(): void
    {
        $kernel = $this->createKernel('empty.yml');

        $object = $kernel->getContainer()->get(ContainerAwareController::class);
        $this->assertInstanceOf(ContainerAwareController::class, $object);
    }

    public function testSymfonyShouldResolveClassesFromDIWithCache(): void
    {
        if (!SourceCache::isSupported()) {
            self::markTestSkipped('APCu is not enabled');
        }

        $kernel = $this->createKernel('empty_with_cache.yml');

        $object = $kernel->getContainer()->get(ContainerAwareController::class);
        $this->assertInstanceOf(ContainerAwareController::class, $object);
    }

    public function testSymfonyShouldResolveClassesFromDIWithCompilation(): void
    {
        $kernel = $this->createKernel('empty_with_compilation.yml');

        $object = $kernel->getContainer()->get(ContainerAwareController::class);
        $this->assertInstanceOf(ContainerAwareController::class, $object);
    }
}
