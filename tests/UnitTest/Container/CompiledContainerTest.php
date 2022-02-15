<?php

/*
 * Symfony Bridge.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/di-symfony-bridge Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\Tests\DI\SymfonyBridge\UnitTest\Container;

use DI\Definition\Definition;
use DI\Definition\Exception\InvalidDefinition;
use DI\Definition\Source\MutableDefinitionSource;
use PHPUnit\Framework\TestCase;
use Teknoo\DI\SymfonyBridge\Container\CompiledContainer;

/**
 * @covers \Teknoo\DI\SymfonyBridge\Container\CompiledContainer
 * @covers \Teknoo\DI\SymfonyBridge\Container\ContainerDefinitionTrait
 */
class CompiledContainerTest extends TestCase
{
    private ?MutableDefinitionSource $originalDefinitions = null;

    /**
     * @return MutableDefinitionSource|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getMutableDefinitionSourceMock(): MutableDefinitionSource
    {
        if (!$this->originalDefinitions instanceof MutableDefinitionSource) {
            $this->originalDefinitions = $this->createMock(MutableDefinitionSource::class);
        }

        return $this->originalDefinitions;
    }

    public function buildInstance(): CompiledContainer
    {
        return new CompiledContainer(
            $this->getMutableDefinitionSourceMock()
        );
    }

    public function testExtractDefinitionWithBadArgument()
    {
        $this->expectException(\TypeError::class);
        $this->buildInstance()->extractDefinition(new \stdClass());
    }

    public function testExtractDefinitionWhenDefinitionsInjected()
    {
        self::assertNull((new CompiledContainer())->extractDefinition('foo'));
    }

    public function testExtractDefinitionWhenNoThereAreNotFound()
    {
        $this->getMutableDefinitionSourceMock()
            ->expects(self::any())
            ->method('getDefinition')
            ->willThrowException(new InvalidDefinition());

        self::assertNull($this->buildInstance()->extractDefinition('foo'));
    }

    public function testExtractDefinition()
    {
        $this->getMutableDefinitionSourceMock()
            ->expects(self::any())
            ->method('getDefinition')
            ->willReturn($this->createMock(Definition::class));

        self::assertInstanceOf(Definition::class, $this->buildInstance()->extractDefinition('foo'));
    }
}
