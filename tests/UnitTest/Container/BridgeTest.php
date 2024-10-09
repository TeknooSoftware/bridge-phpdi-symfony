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

namespace Teknoo\Tests\DI\SymfonyBridge\UnitTest\Container;

use DI\Container as DIContainer;
use DI\ContainerBuilder as DIContainerBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
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

    /**
     * @return DIContainerBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getDiBuilderMock(): DIContainerBuilder
    {
        if (!$this->diBuilder instanceof DIContainerBuilder) {
            $this->diBuilder = $this->createMock(DIContainerBuilder::class);
        }

        return $this->diBuilder;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|SfContainer
     */
    private function getSfContainerMock(): SfContainer
    {
        if (!$this->sfContainer instanceof SfContainer) {
            $this->sfContainer = $this->createMock(SfContainer::class);
        }

        return $this->sfContainer;
    }

    public function buildInstance(?string $compilationPath = null, bool $enableCache = false): Bridge
    {
        return new Bridge(
            $this->getDiBuilderMock(),
            $this->getSfContainerMock(),
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

    public function testInvokeWhenTheServiceIdIsInvalid()
    {
        $this->expectException(\TypeError::class);

        $bridge = $this->buildInstance();
        $bridge(new \stdClass());
    }

    public function testInvokeWhenTheServiceIdWasNotFound()
    {
        $this->expectException(ServiceNotFoundException::class);

        $this->getDiBuilderMock()
            ->expects($this->any())
            ->method('build')
            ->willReturn($this->createMock(DIContainer::class));

        $bridge = $this->buildInstance();
        $bridge('foo');
    }

    public function testInvokeWhenTheServiceIdWasFound()
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

        $this->getDiBuilderMock()
            ->expects($this->any())
            ->method('build')
            ->willReturn($container);

        $bridge = $this->buildInstance();
        self::assertInstanceOf(\stdClass::class, $bridge('foo'));
    }

    public function testInvokeWhenTheServiceIdWasFoundWithCompilationPath()
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

        $this->getDiBuilderMock()
            ->expects($this->any())
            ->method('build')
            ->willReturn($container);

        $bridge = $this->buildInstance('/foo/bar', false);
        self::assertInstanceOf(\stdClass::class, $bridge('foo'));
    }

    public function testInvokeWhenTheServiceIdWasFoundWithCache()
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

        $this->getDiBuilderMock()
            ->expects($this->any())
            ->method('build')
            ->willReturn($container);

        $bridge = $this->buildInstance(null, true);
        self::assertInstanceOf(\stdClass::class, $bridge('foo'));
    }

    public function testInvokeWhenTheServiceIdWasFoundAndIsContainerAware()
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

        $this->getDiBuilderMock()
            ->expects($this->any())
            ->method('build')
            ->willReturn($container);

        $bridge = $this->buildInstance();
        self::assertInstanceOf(ContainerAwareInterface::class, $bridge('foo'));
    }

    public function testInvokeWhenTheServiceIdWasFoundAndMustBeFetchedFromContainerEachTime()
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

        $this->getDiBuilderMock()
            ->expects($this->any())
            ->method('build')
            ->willReturn($container);

        $bridge = $this->buildInstance();
        self::assertInstanceOf(\stdClass::class, $bridge('foo'));
        self::assertInstanceOf(\stdClass::class, $bridge('foo'));
    }

    public function testGetWithAnEntryExistNowhere()
    {
        $this->getSfContainerMock()
            ->expects($this->any())
            ->method('has')
            ->willReturn(false);

        $this->getSfContainerMock()
            ->expects($this->any())
            ->method('hasParameter')
            ->willReturn(false);

        $container = $this->createMock(DIContainer::class);
        $container->expects($this->any())
            ->method('has')
            ->willReturn(false);

        $this->getDiBuilderMock()
            ->expects($this->any())
            ->method('build')
            ->willReturn($container);

        $this->expectException(ServiceNotFoundException::class);
        $this->buildInstance()->get('foo');
    }

    public function testGetWithAnEntryExistAsServiceInSymfony()
    {
        $this->getSfContainerMock()
            ->expects($this->any())
            ->method('has')
            ->willReturn(true);

        $this->getSfContainerMock()
            ->expects($this->any())
            ->method('get')
            ->willReturn(new \stdClass());

        $this->getSfContainerMock()
            ->expects($this->any())
            ->method('hasParameter')
            ->willReturn(false);

        $this->getSfContainerMock()
            ->expects($this->never())
            ->method('getParameter');

        $container = $this->createMock(DIContainer::class);
        $container->expects($this->any())
            ->method('has')
            ->willReturn(false);

        $container->expects($this->never())
            ->method('get');

        $this->getDiBuilderMock()
            ->expects($this->any())
            ->method('build')
            ->willReturn($container);

        self::assertInstanceOf(\stdClass::class, $this->buildInstance()->get('foo'));
    }

    public function testGetWithAnEntryExistInPHPDI()
    {
        $this->getSfContainerMock()
            ->expects($this->any())
            ->method('has')
            ->willReturn(false);

        $this->getSfContainerMock()
            ->expects($this->never())
            ->method('get');

        $this->getSfContainerMock()
            ->expects($this->any())
            ->method('hasParameter')
            ->willReturn(false);

        $this->getSfContainerMock()
            ->expects($this->never())
            ->method('getParameter');

        $container = $this->createMock(DIContainer::class);
        $container->expects($this->any())
            ->method('has')
            ->willReturn(true);

        $container->expects($this->once())
            ->method('get')
            ->willReturn(new \stdClass());

        $this->getDiBuilderMock()
            ->expects($this->any())
            ->method('build')
            ->willReturn($container);

        self::assertInstanceOf(\stdClass::class, $this->buildInstance()->get('foo'));
    }

    public function testGetWithAnEntryExistAsParameterInSymfony()
    {
        $this->getSfContainerMock()
            ->expects($this->any())
            ->method('has')
            ->willReturn(false);

        $this->getSfContainerMock()
            ->expects($this->never())
            ->method('get');

        $this->getSfContainerMock()
            ->expects($this->any())
            ->method('hasParameter')
            ->willReturn(true);

        $this->getSfContainerMock()
            ->expects($this->once())
            ->method('getParameter')
            ->willReturn('bar');

        $container = $this->createMock(DIContainer::class);
        $container->expects($this->any())
            ->method('has')
            ->willReturn(false);

        $container->expects($this->never())
            ->method('get');

        $this->getDiBuilderMock()
            ->expects($this->any())
            ->method('build')
            ->willReturn($container);

        self::assertEquals('bar', $this->buildInstance()->get('foo'));
    }

    public function testHasWithAnEntryExistNowhere()
    {
        $this->getSfContainerMock()
            ->expects($this->any())
            ->method('has')
            ->willReturn(false);

        $this->getSfContainerMock()
            ->expects($this->any())
            ->method('hasParameter')
            ->willReturn(false);

        $container = $this->createMock(DIContainer::class);
        $container->expects($this->any())
            ->method('has')
            ->willReturn(false);

        $this->getDiBuilderMock()
            ->expects($this->any())
            ->method('build')
            ->willReturn($container);

        self::assertFalse($this->buildInstance()->has('foo'));
    }

    public function testHasWithAnEntryExistAsServiceInSymfony()
    {
        $this->getSfContainerMock()
            ->expects($this->any())
            ->method('has')
            ->willReturn(true);

        $this->getSfContainerMock()
            ->expects($this->any())
            ->method('hasParameter')
            ->willReturn(false);

        $container = $this->createMock(DIContainer::class);
        $container->expects($this->any())
            ->method('has')
            ->willReturn(false);

        $this->getDiBuilderMock()
            ->expects($this->any())
            ->method('build')
            ->willReturn($container);

        self::assertTrue($this->buildInstance()->has('foo'));
    }

    public function testHasWithAnEntryExistInPHPDI()
    {
        $this->getSfContainerMock()
            ->expects($this->any())
            ->method('has')
            ->willReturn(false);

        $this->getSfContainerMock()
            ->expects($this->any())
            ->method('hasParameter')
            ->willReturn(false);

        $container = $this->createMock(DIContainer::class);
        $container->expects($this->any())
            ->method('has')
            ->willReturn(true);

        $this->getDiBuilderMock()
            ->expects($this->any())
            ->method('build')
            ->willReturn($container);

        self::assertTrue($this->buildInstance()->has('foo'));
    }

    public function testHasWithAnEntryExistAsParameterInSymfony()
    {
        $this->getSfContainerMock()
            ->expects($this->any())
            ->method('has')
            ->willReturn(false);

        $this->getSfContainerMock()
            ->expects($this->any())
            ->method('hasParameter')
            ->willReturn(true);

        $container = $this->createMock(DIContainer::class);
        $container->expects($this->any())
            ->method('has')
            ->willReturn(false);

        $this->getDiBuilderMock()
            ->expects($this->any())
            ->method('build')
            ->willReturn($container);

        self::assertTrue($this->buildInstance()->has('foo'));
    }
}
