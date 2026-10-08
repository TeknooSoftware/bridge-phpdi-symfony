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
 *
 * @link        https://teknoo.software/libraries/php-di-symfony-bridge Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\DI\SymfonyBridge\UnitTest\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Teknoo\DI\SymfonyBridge\DependencyInjection\DIBridgeExtension;
use Teknoo\DI\SymfonyBridge\Extension\InvalidExtensionException;
use Teknoo\Tests\DI\SymfonyBridge\UnitTest\DependencyInjection\Support\ExtensionMock;
use Teknoo\Tests\DI\SymfonyBridge\UnitTest\DependencyInjection\Support\InvalidExtensionMock;
use TypeError;

#[CoversClass(DIBridgeExtension::class)]
class DIBridgeExtensionTest extends TestCase
{
    private ?ContainerBuilder $container = null;

    private function getContainerBuilderStub(): ContainerBuilder&Stub
    {
        if (!$this->container instanceof ContainerBuilder) {
            $this->container = $this->createStub(ContainerBuilder::class);
        }

        return $this->container;
    }

    public function buildInstance(): DIBridgeExtension
    {
        return new DIBridgeExtension(BuilderFake::class);
    }

    public function testLoadWithoutDefinitionsAndImport(): void
    {
        $this->buildInstance()->load([], $this->getContainerBuilderStub());
        $this->assertTrue(true);
    }

    public function testLoadWithDefinitionsAndImportWithDefaultValues(): void
    {
        $this->buildInstance()->load(
            [
                [
                    'definitions' => ['foo', 'bar'],
                    'import' => ['hello' => 'world'],
                ]
            ],
            $this->getContainerBuilderStub()
        );
        $this->assertTrue(true);
    }

    public function testLoadWithDefinitionsAndImport(): void
    {
        $this->buildInstance()->load(
            [
                [
                    'compilation_path' => '/foo/bar',
                    'enable_cache' => true,
                    'definitions' => ['foo', 'bar'],
                    'import' => ['hello' => 'world'],
                ]
            ],
            $this->getContainerBuilderStub()
        );
        $this->assertTrue(true);
    }

    public function testLoadWithExtensions(): void
    {
        $this->container = $this->createMock(ContainerBuilder::class);
        $mock = $this->getContainerBuilderStub();
        $mock->expects($this->exactly(2))
            ->method('has')
            ->willReturnMap([
                ['ext-foo', true],
                [ExtensionMock::class, false]
            ]);

        $ext = ExtensionMock::create();
        $ext->counter = 0;

        $mock->expects($this->once())
            ->method('get')
            ->with('ext-foo')
            ->willReturn($ext);

        $this->buildInstance()->load(
            [
                [
                    'compilation_path' => '/foo/bar',
                    'enable_cache' => true,
                    'definitions' => ['foo', 'bar'],
                    'import' => ['hello' => 'world'],
                    'extensions' => [
                        'ext-foo',
                        [
                            'priority' => 1,
                            'name' => ExtensionMock::class
                        ]
                    ]
                ]
            ],
            $mock
        );

        $this->assertEquals(2, $ext->counter);
    }

    private function buildContainerWithServiceExtension(string $serviceClass, DIBridgeExtension $extension): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->registerExtension($extension);
        $container->register('ext.svc', $serviceClass)
            ->setFactory([$serviceClass, 'create'])
            ->setPublic(true);
        $container->loadFromExtension(
            'di_bridge',
            [
                'extensions' => [
                    'ext.svc',
                    ['priority' => 1, 'name' => ExtensionMock::class],
                    'service.not.found',
                ],
            ],
        );

        return $container;
    }

    public function testPrependPreloadsExtensionsDeclaredByServiceIdForLoad(): void
    {
        $extension = $this->buildInstance();
        $container = $this->buildContainerWithServiceExtension(ExtensionMock::class, $extension);

        $ext = ExtensionMock::create();
        $ext->counter = 0;

        $extension->prepend($container);

        //Like the temporary container passed by Symfony to load(), without any application's services
        $this->container = $this->createMock(ContainerBuilder::class);
        $mock = $this->getContainerBuilderStub();
        $mock->method('has')->willReturn(false);
        $mock->expects($this->never())->method('get');

        $extension->load(
            [
                [
                    'extensions' => [
                        'ext.svc',
                        ['priority' => 1, 'name' => ExtensionMock::class],
                    ],
                ],
            ],
            $mock
        );

        //Once from the service, once from the class name
        $this->assertEquals(2, $ext->counter);
    }

    public function testPrependWithAServiceNotImplementingTheExtensionInterface(): void
    {
        $extension = $this->buildInstance();
        $container = $this->buildContainerWithServiceExtension(InvalidExtensionMock::class, $extension);

        $extension->prepend($container);

        $stub = $this->getContainerBuilderStub();
        $stub->method('has')->willReturn(false);

        $this->expectException(InvalidExtensionException::class);
        $extension->load([['extensions' => ['ext.svc']]], $stub);
    }

    public function testPrependWithoutExtensionsConfigured(): void
    {
        $extension = $this->buildInstance();
        $container = new ContainerBuilder();
        $container->registerExtension($extension);
        $container->loadFromExtension('di_bridge', ['definitions' => ['foo']]);

        $extension->prepend($container);

        $this->container = $this->createMock(ContainerBuilder::class);
        $mock = $this->getContainerBuilderStub();
        $mock->expects($this->never())->method('has');
        $mock->expects($this->never())->method('get');

        $extension->load([['definitions' => ['foo']]], $mock);
        $this->assertTrue(true);
    }

    public function testExceptionOnLoadWithExtensionsWithInvalidService(): void
    {
        $this->container = $this->createMock(ContainerBuilder::class);
        $mock = $this->getContainerBuilderStub();
        $mock->expects($this->exactly(1))
            ->method('has')
            ->willReturnMap([
                ['ext-foo', true],
            ]);

        $mock->expects($this->once())
            ->method('get')
            ->with('ext-foo')
            ->willReturn(new stdClass());

        $this->expectException(InvalidExtensionException::class);
        $this->buildInstance()->load(
            [
                [
                    'compilation_path' => '/foo/bar',
                    'enable_cache' => true,
                    'definitions' => ['foo', 'bar'],
                    'import' => ['hello' => 'world'],
                    'extensions' => [
                        'ext-foo',
                    ]
                ]
            ],
            $mock
        );
    }

    public function testExceptionOnLoadWithExtensionsWithInvalidClass(): void
    {
        $this->container = $this->createMock(ContainerBuilder::class);
        $mock = $this->getContainerBuilderStub();
        $mock->expects($this->exactly(1))
            ->method('has')
            ->willReturn(false);

        $mock->expects($this->never())
            ->method('get');

        $this->expectException(InvalidExtensionException::class);
        $this->buildInstance()->load(
            [
                [
                    'compilation_path' => '/foo/bar',
                    'enable_cache' => true,
                    'definitions' => ['foo', 'bar'],
                    'import' => ['hello' => 'world'],
                    'extensions' => [
                        stdClass::class,
                    ]
                ]
            ],
            $mock
        );
    }

    public function testLoadErrorContainer(): void
    {
        $this->expectException(TypeError::class);
        $this->buildInstance()->load([], new stdClass());
    }

    public function testLoadErrorConfig(): void
    {
        $this->expectException(TypeError::class);
        $this->buildInstance()->load(new stdClass(), $this->getContainerBuilderStub());
    }
}
