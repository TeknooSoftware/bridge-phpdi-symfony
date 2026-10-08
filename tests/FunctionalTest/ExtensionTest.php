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

use PHPUnit\Framework\Attributes\CoversNothing;
use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\Extension;
use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\Product;

/**
 * Checks that bridge's extensions declared by a Symfony service id or by a class name are called during the
 * Symfony container compilation.
 */
#[CoversNothing]
class ExtensionTest extends AbstractFunctionalTests
{
    protected function setUp(): void
    {
        parent::setUp();
        Extension::$configuredCounter = 0;
    }

    public function testExtensionDeclaredByASymfonyServiceIdIsCalled(): void
    {
        $kernel = $this->createKernel('extension_service.yml');
        $container = $kernel->getContainer();

        $this->assertSame(1, Extension::$configuredCounter);
        $this->assertTrue($container->has('extension.product'));
        $this->assertInstanceOf(Product::class, $container->get('extension.product'));
    }

    public function testExtensionDeclaredByAClassNameIsCalled(): void
    {
        $kernel = $this->createKernel('extension_class.yml');
        $container = $kernel->getContainer();

        $this->assertSame(1, Extension::$configuredCounter);
        $this->assertTrue($container->has('extension.product'));
        $this->assertInstanceOf(Product::class, $container->get('extension.product'));
    }
}
