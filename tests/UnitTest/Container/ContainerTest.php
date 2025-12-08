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

use DI\Definition\Definition;
use DI\Definition\Exception\InvalidDefinition;
use DI\Definition\Source\MutableDefinitionSource;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\DI\SymfonyBridge\Container\Container;

#[CoversClass(Container::class)]
class ContainerTest extends TestCase
{
    private ?MutableDefinitionSource $originalDefinitions = null;

    private function getMutableDefinitionSourceStub(): MutableDefinitionSource&Stub
    {
        if (!$this->originalDefinitions instanceof MutableDefinitionSource) {
            $this->originalDefinitions = $this->createStub(MutableDefinitionSource::class);
        }

        return $this->originalDefinitions;
    }

    public function buildInstance(): Container
    {
        return new Container(
            $this->getMutableDefinitionSourceStub()
        );
    }

    public function testExtractDefinitionWithBadArgument(): void
    {
        $this->expectException(\TypeError::class);
        $this->buildInstance()->extractDefinition(new \stdClass());
    }

    public function testExtractDefinitionWhenDefinitionsInjected(): void
    {
        $this->assertNotInstanceOf(\DI\Definition\Definition::class, new Container()->extractDefinition('foo'));
    }

    public function testExtractDefinitionWhenNoThereAreNotFound(): void
    {
        $this->getMutableDefinitionSourceStub()
            ->method('getDefinition')
            ->willThrowException(new InvalidDefinition());

        $this->assertNotInstanceOf(\DI\Definition\Definition::class, $this->buildInstance()->extractDefinition('foo'));
    }

    public function testExtractDefinition(): void
    {
        $this->getMutableDefinitionSourceStub()
            ->method('getDefinition')
            ->willReturn($this->createStub(Definition::class));

        $this->assertInstanceOf(Definition::class, $this->buildInstance()->extractDefinition('foo'));
    }
}
