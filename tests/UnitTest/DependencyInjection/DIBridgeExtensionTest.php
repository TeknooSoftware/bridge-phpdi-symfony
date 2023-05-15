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

namespace Teknoo\Tests\DI\SymfonyBridge\UnitTest\DependencyInjection;

use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Teknoo\DI\SymfonyBridge\Container\BridgeBuilder;
use Teknoo\DI\SymfonyBridge\DependencyInjection\DIBridgeExtension;
use TypeError;

/**
 * @covers \Teknoo\DI\SymfonyBridge\DependencyInjection\DIBridgeExtension
 */
class DIBridgeExtensionTest extends TestCase
{
    private ?ContainerBuilder $container = null;

    /**
     * @return ContainerBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getContainerBuilderMock()
    {
        if (!$this->container instanceof ContainerBuilder) {
            $this->container = $this->createMock(ContainerBuilder::class);
        }

        return $this->container;
    }

    public function buildInstance(): DIBridgeExtension
    {
        $mock = $this->createMock(BridgeBuilder::class);
        return new DIBridgeExtension($mock::class);
    }

    public function testLoadWithoutDefinitionsAndImport()
    {
        self::assertInstanceOf(
            DIBridgeExtension::class,
            $this->buildInstance()->load([], $this->getContainerBuilderMock())
        );
    }

    public function testLoadWithDefinitionsAndImportWithDefaultValues()
    {
        self::assertInstanceOf(
            DIBridgeExtension::class,
            $this->buildInstance()->load(
                [
                    [
                        'definitions' => ['foo', 'bar'],
                        'import' => ['hello' => 'world'],
                    ]
                ],
                $this->getContainerBuilderMock()
            )
        );
    }

    public function testLoadWithDefinitionsAndImport()
    {
        self::assertInstanceOf(
            DIBridgeExtension::class,
            $this->buildInstance()->load(
                [
                    [
                        'compilation_path' => '/foo/bar',
                        'enable_cache' => true,
                        'definitions' => ['foo', 'bar'],
                        'import' => ['hello' => 'world'],
                    ]
                ],
                $this->getContainerBuilderMock()
            )
        );
    }

    public function testLoadErrorContainer()
    {
        $this->expectException(TypeError::class);
        $this->buildInstance()->load([], new stdClass());
    }

    public function testLoadErrorConfig()
    {
        $this->expectException(TypeError::class);
        $this->buildInstance()->load(new stdClass(), $this->getContainerBuilderMock());
    }
}
