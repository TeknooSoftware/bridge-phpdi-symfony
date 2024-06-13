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

use DI\Definition\Definition;
use DI\Definition\Exception\InvalidDefinition;
use DI\Definition\Source\MutableDefinitionSource;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;
use Teknoo\DI\SymfonyBridge\Container\Container;
use Teknoo\DI\SymfonyBridge\Container\ContainerDefinitionTrait;

#[CoversClass(Container::class)]
#[CoversTrait(ContainerDefinitionTrait::class)]
class ContainerTest extends TestCase
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

    public function buildInstance(): Container
    {
        return new Container(
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
        self::assertNull((new Container())->extractDefinition('foo'));
    }

    public function testExtractDefinitionWhenNoThereAreNotFound()
    {
        $this->getMutableDefinitionSourceMock()
            ->expects($this->any())
            ->method('getDefinition')
            ->willThrowException(new InvalidDefinition());

        self::assertNull($this->buildInstance()->extractDefinition('foo'));
    }

    public function testExtractDefinition()
    {
        $this->getMutableDefinitionSourceMock()
            ->expects($this->any())
            ->method('getDefinition')
            ->willReturn($this->createMock(Definition::class));

        self::assertInstanceOf(Definition::class, $this->buildInstance()->extractDefinition('foo'));
    }
}
