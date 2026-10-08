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

use DI\Container as DIContainer;
use PHPUnit\Framework\Attributes\CoversNothing;
use Psr\Container\ContainerInterface;
use Teknoo\DI\SymfonyBridge\Container\Bridge;
use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\Class1;
use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\Class3;
use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\ContainerAwareController;

/**
 * Checks that every entry defined in the PHP-DI definitions files is registered as a public Symfony service
 * during the Symfony container compilation.
 */
#[CoversNothing]
class BridgeRegistrationTest extends AbstractFunctionalTests
{
    public function testAllPhpDiEntriesAreRegisteredAsPublicSymfonyServices(): void
    {
        $kernel = $this->createKernel('empty.yml');
        $container = $kernel->getContainer();

        foreach ([Class1::class, Class3::class, ContainerAwareController::class] as $id) {
            $this->assertTrue($container->has($id), "Service $id must be registered into Symfony");
        }

        $this->assertInstanceOf(ContainerAwareController::class, $container->get(ContainerAwareController::class));
    }

    public function testBridgeIsNotExposedAsPublicService(): void
    {
        $kernel = $this->createKernel('empty.yml');

        $this->assertFalse($kernel->getContainer()->has(Bridge::class));
    }

    public function testPhpDiInternalEntriesAreNotExposedAsSymfonyServices(): void
    {
        $kernel = $this->createKernel('empty.yml');
        $container = $kernel->getContainer();

        foreach ([\DI\FactoryInterface::class, \Invoker\InvokerInterface::class] as $id) {
            $this->assertFalse($container->has($id), "Internal PHP-DI entry $id must not be registered into Symfony");
        }
    }

    public function testPsrContainerAndPhpDiContainerAreExposedAsPublicSymfonyServices(): void
    {
        $kernel = $this->createKernel('empty.yml');
        $container = $kernel->getContainer();

        $this->assertTrue($container->has(ContainerInterface::class));
        $this->assertTrue($container->has(DIContainer::class));

        $bridge = $container->get(ContainerInterface::class);
        $this->assertInstanceOf(Bridge::class, $bridge);

        $diContainer = $container->get(DIContainer::class);
        $this->assertInstanceOf(DIContainer::class, $diContainer);

        //An entry set at runtime into the PHP-DI container must be resolvable through the bridge
        $diContainer->set('runtime.entry', 'bar');
        $this->assertSame('bar', $bridge->get('runtime.entry'));
    }
}
