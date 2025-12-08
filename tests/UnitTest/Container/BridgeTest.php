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

namespace Teknoo\Tests\DI\SymfonyBridge\UnitTest\Container;

use DI\Container as DIContainer;
use DI\ContainerBuilder as DIContainerBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container as SfContainer;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Teknoo\DI\SymfonyBridge\Container\Bridge;

use function interface_exists;

#[CoversClass(Bridge::class)]
class BridgeTest extends TestCase
{
    private ?DIContainerBuilder $diBuilder = null;

    private ?SfContainer $sfContainer = null;

    private function getDiBuilderStub(): DIContainerBuilder&Stub
    {
        if (!$this->diBuilder instanceof DIContainerBuilder) {
            $this->diBuilder = $this->createStub(DIContainerBuilder::class);
        }

        return $this->diBuilder;
    }

    private function getSfContainerStub(): SfContainer&Stub
    {
        if (!$this->sfContainer instanceof SfContainer) {
            $this->sfContainer = $this->createStub(SfContainer::class);
        }

        return $this->sfContainer;
    }

    public function buildInstance(?string $compilationPath = null, bool $enableCache = false): Bridge
    {
        return new Bridge(
            $this->getDiBuilderStub(),
            $this->getSfContainerStub(),
            [
                'foo',
                'bar'
            ],
            [
                'hello' => 'world'
            ],
            $compilationPath,
            $enableCache
        );
    }

    public function testInvokeWhenTheServiceIdIsInvalid(): void
    {
        $this->expectException(\TypeError::class);

        $bridge = $this->buildInstance();
        $bridge(new \stdClass());
    }

    public function testInvokeWhenTheServiceIdWasNotFound(): void
    {
        $this->expectException(ServiceNotFoundException::class);

        $this->getDiBuilderStub()
            ->method('build')
            ->willReturn($this->createStub(DIContainer::class));

        $bridge = $this->buildInstance();
        $bridge('foo');
    }

    public function testInvokeWhenTheServiceIdWasFound(): void
    {
        $container = $this->createMock(DIContainer::class);

        $container->expects($this->once())
            ->method('has')
            ->with('foo')
            ->willReturn(true);

        $container->expects($this->once())
            ->method('get')
            ->with('foo')
            ->willReturn(new \stdClass());

        $this->getDiBuilderStub()
            ->method('build')
            ->willReturn($container);

        $bridge = $this->buildInstance();
        $this->assertInstanceOf(\stdClass::class, $bridge('foo'));
    }

    public function testInvokeWhenTheServiceIdWasFoundWithCompilationPath(): void
    {
        $container = $this->createMock(DIContainer::class);

        $container->expects($this->once())
            ->method('has')
            ->with('foo')
            ->willReturn(true);

        $container->expects($this->once())
            ->method('get')
            ->with('foo')
            ->willReturn(new \stdClass());

        $this->getDiBuilderStub()
            ->method('build')
            ->willReturn($container);

        $bridge = $this->buildInstance('/foo/bar', false);
        $this->assertInstanceOf(\stdClass::class, $bridge('foo'));
    }

    public function testInvokeWhenTheServiceIdWasFoundWithCache(): void
    {
        $container = $this->createMock(DIContainer::class);

        $container->expects($this->once())
            ->method('has')
            ->with('foo')
            ->willReturn(true);

        $container->expects($this->once())
            ->method('get')
            ->with('foo')
            ->willReturn(new \stdClass());

        $this->getDiBuilderStub()
            ->method('build')
            ->willReturn($container);

        $bridge = $this->buildInstance(null, true);
        $this->assertInstanceOf(\stdClass::class, $bridge('foo'));
    }

    public function testInvokeWhenTheServiceIdWasFoundAndIsContainerAware(): void
    {
        if (!interface_exists(ContainerAwareInterface::class)) {
            self::markTestSkipped('Test only for Symfony prior to 7');

            return;
        }

        $container = $this->createMock(DIContainer::class);

        $container->expects($this->once())
            ->method('has')
            ->with('foo')
            ->willReturn(true);

        $instance = $this->createMock(ContainerAwareInterface::class);
        $instance->expects($this->once())
            ->method('setContainer')
            ->willReturnSelf();

        $container->expects($this->once())
            ->method('get')
            ->with('foo')
            ->willReturn($instance);

        $this->getDiBuilderStub()
            ->method('build')
            ->willReturn($container);

        $bridge = $this->buildInstance();
        $this->assertInstanceOf(ContainerAwareInterface::class, $bridge('foo'));
    }

    public function testInvokeWhenTheServiceIdWasFoundAndMustBeFetchedFromContainerEachTime(): void
    {
        $container = $this->createMock(DIContainer::class);

        $container->expects($this->exactly(2))
            ->method('has')
            ->with('foo')
            ->willReturn(true);

        $container->expects($this->exactly(2))
            ->method('get')
            ->with('foo')
            ->willReturn(new \stdClass());

        $this->getDiBuilderStub()
            ->method('build')
            ->willReturn($container);

        $bridge = $this->buildInstance();
        $this->assertInstanceOf(\stdClass::class, $bridge('foo'));
        $this->assertInstanceOf(\stdClass::class, $bridge('foo'));
    }

    public function testGetWithAnEntryExistNowhere(): void
    {
        $this->getSfContainerStub()
            ->method('has')
            ->willReturn(false);

        $this->getSfContainerStub()
            ->method('hasParameter')
            ->willReturn(false);

        $container = $this->createStub(DIContainer::class);
        $container
            ->method('has')
            ->willReturn(false);

        $this->getDiBuilderStub()
            ->method('build')
            ->willReturn($container);

        $this->expectException(ServiceNotFoundException::class);
        $this->buildInstance()->get('foo');
    }

    public function testGetWithAnEntryExistAsServiceInSymfony(): void
    {
        $this->sfContainer = $this->createMock(SfContainer::class);
        $this->getSfContainerStub()
            ->method('has')
            ->willReturn(true);

        $this->getSfContainerStub()
            ->method('get')
            ->willReturn(new \stdClass());

        $this->getSfContainerStub()
            ->method('hasParameter')
            ->willReturn(false);

        $this->getSfContainerStub()
            ->expects($this->never())
            ->method('getParameter');

        $container = $this->createMock(DIContainer::class);
        $container
            ->method('has')
            ->willReturn(false);

        $container->expects($this->never())
            ->method('get');

        $this->getDiBuilderStub()
            ->method('build')
            ->willReturn($container);

        $this->assertInstanceOf(\stdClass::class, $this->buildInstance()->get('foo'));
    }

    public function testGetWithAnEntryExistInPHPDI(): void
    {
        $this->sfContainer = $this->createMock(SfContainer::class);
        $this->getSfContainerStub()
            ->method('has')
            ->willReturn(false);

        $this->getSfContainerStub()
            ->expects($this->never())
            ->method('get');

        $this->getSfContainerStub()
            ->method('hasParameter')
            ->willReturn(false);

        $this->getSfContainerStub()
            ->expects($this->never())
            ->method('getParameter');

        $container = $this->createMock(DIContainer::class);
        $container
            ->method('has')
            ->willReturn(true);

        $container->expects($this->once())
            ->method('get')
            ->willReturn(new \stdClass());

        $this->getDiBuilderStub()
            ->method('build')
            ->willReturn($container);

        $this->assertInstanceOf(\stdClass::class, $this->buildInstance()->get('foo'));
    }

    public function testGetWithAnEntryExistAsParameterInSymfony(): void
    {
        $this->sfContainer = $this->createMock(SfContainer::class);
        $this->getSfContainerStub()
            ->method('has')
            ->willReturn(false);

        $this->getSfContainerStub()
            ->expects($this->never())
            ->method('get');

        $this->getSfContainerStub()
            ->method('hasParameter')
            ->willReturn(true);

        $this->getSfContainerStub()
            ->expects($this->once())
            ->method('getParameter')
            ->willReturn('bar');

        $container = $this->createMock(DIContainer::class);
        $container
            ->method('has')
            ->willReturn(false);

        $container->expects($this->never())
            ->method('get');

        $this->getDiBuilderStub()
            ->method('build')
            ->willReturn($container);

        $this->assertEquals('bar', $this->buildInstance()->get('foo'));
    }

    public function testHasWithAnEntryExistNowhere(): void
    {
        $this->getSfContainerStub()
            ->method('has')
            ->willReturn(false);

        $this->getSfContainerStub()
            ->method('hasParameter')
            ->willReturn(false);

        $container = $this->createStub(DIContainer::class);
        $container
            ->method('has')
            ->willReturn(false);

        $this->getDiBuilderStub()
            ->method('build')
            ->willReturn($container);

        $this->assertFalse($this->buildInstance()->has('foo'));
    }

    public function testHasWithAnEntryExistAsServiceInSymfony(): void
    {
        $this->getSfContainerStub()
            ->method('has')
            ->willReturn(true);

        $this->getSfContainerStub()
            ->method('hasParameter')
            ->willReturn(false);

        $container = $this->createStub(DIContainer::class);
        $container
            ->method('has')
            ->willReturn(false);

        $this->getDiBuilderStub()
            ->method('build')
            ->willReturn($container);

        $this->assertTrue($this->buildInstance()->has('foo'));
    }

    public function testHasWithAnEntryExistInPHPDI(): void
    {
        $this->getSfContainerStub()
            ->method('has')
            ->willReturn(false);

        $this->getSfContainerStub()
            ->method('hasParameter')
            ->willReturn(false);

        $container = $this->createStub(DIContainer::class);
        $container
            ->method('has')
            ->willReturn(true);

        $this->getDiBuilderStub()
            ->method('build')
            ->willReturn($container);

        $this->assertTrue($this->buildInstance()->has('foo'));
    }

    public function testHasWithAnEntryExistAsParameterInSymfony(): void
    {
        $this->getSfContainerStub()
            ->method('has')
            ->willReturn(false);

        $this->getSfContainerStub()
            ->method('hasParameter')
            ->willReturn(true);

        $container = $this->createStub(DIContainer::class);
        $container
            ->method('has')
            ->willReturn(false);

        $this->getDiBuilderStub()
            ->method('build')
            ->willReturn($container);

        $this->assertTrue($this->buildInstance()->has('foo'));
    }
}
