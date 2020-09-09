<?php

/*
 * Symfony Bridge.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/di-symfony-bridge Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\Tests\DI\SymfonyBridge\UnitTest\Container;

use DI\ContainerBuilder as DIContainerBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder as SfContainerBuilder;
use Teknoo\DI\SymfonyBridge\Container\BridgeBuilder;

/**
 * @covers \Teknoo\DI\SymfonyBridge\Container\BridgeBuilder
 * @covers \Teknoo\DI\SymfonyBridge\Container\BridgeTrait
 */
class BridgeBuilderTest extends TestCase
{
    private ?DIContainerBuilder $diBuilder = null;

    private ?SfContainerBuilder $sfContainer = null;

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
     * @return \PHPUnit\Framework\MockObject\MockObject|SfContainerBuilder
     */
    private function getSfContainerBuilderMock(): SfContainerBuilder
    {
        if (!$this->sfContainer instanceof SfContainerBuilder) {
            $this->sfContainer = $this->createMock(SfContainerBuilder::class);
        }

        return $this->sfContainer;
    }

    public function buildInstance(): BridgeBuilder
    {
        return new BridgeBuilder(
            $this->getDiBuilderMock(),
            $this->getSfContainerBuilderMock()
        );
    }

    public function testLoadDefinition()
    {
        self::assertInstanceOf(
            BridgeBuilder::class,
            $this->buildInstance()->loadDefinition(['foo', 'bar'])
        );
    }
}
