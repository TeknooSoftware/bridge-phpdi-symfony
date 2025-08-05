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
use DI\Definition\ArrayDefinition;
use DI\Definition\EnvironmentVariableDefinition;
use DI\Definition\FactoryDefinition;
use DI\Definition\ObjectDefinition;
use DI\Definition\Reference as DIReference;
use DI\Definition\StringDefinition;
use DI\Definition\ValueDefinition;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder as SfContainerBuilder;
use Symfony\Component\DependencyInjection\Definition as SfDefinition;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference as SfReference;
use Teknoo\DI\SymfonyBridge\Container\Bridge;
use Teknoo\DI\SymfonyBridge\Container\BridgeBuilder;
use Teknoo\DI\SymfonyBridge\Container\Container;

use function func_get_args;

#[CoversClass(BridgeBuilder::class)]
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

    public function testLoadDefinitionWithBadArgument(): void
    {
        $this->expectException(\TypeError::class);

        $this->buildInstance()->loadDefinition(new \stdClass());
    }

    public function testLoadDefinition(): void
    {
        $this->assertInstanceOf(BridgeBuilder::class, $this->buildInstance()->loadDefinition([
            ['priority' => 0, 'file' => 'foo'],
            ['priority' => 0, 'file' => 'bar']
        ]));
    }

    public function testprepareCompilationWithBadArgument(): void
    {
        $this->expectException(\TypeError::class);

        $this->buildInstance()->prepareCompilation(new \stdClass());
    }

    public function testprepareCompilation(): void
    {
        $this->assertInstanceOf(BridgeBuilder::class, $this->buildInstance()->prepareCompilation('foo'));
    }

    public function testenableCacheWithBadArgument(): void
    {
        $this->expectException(\TypeError::class);

        $this->buildInstance()->enableCache(new \stdClass());
    }

    public function testenableCache(): void
    {
        $this->assertInstanceOf(BridgeBuilder::class, $this->buildInstance()->enableCache(true));
    }

    public function testImportWithBadArgument(): void
    {
        $this->expectException(\TypeError::class);

        $this->buildInstance()->import(new \stdClass(), new \stdClass());
    }

    public function testImport(): void
    {
        $this->assertInstanceOf(BridgeBuilder::class, $this->buildInstance()->import('foo', 'bar'));
    }

    public function testInitializeSymfonyContainerWithDefaultDIContainer(): void
    {
        $this->expectException(\RuntimeException::class);

        $container = $this->createMock(DIContainer::class);

        $this->getDiBuilderMock()
            ->method('build')
            ->willReturn($container);

        $this->buildInstance()->initializeSymfonyContainer();
    }

    public function testInitializeSymfonyContainerWithNotFoundEntry(): void
    {
        $definitionsFiles = [
            ['priority' => 0, 'file' => 'foo'],
            ['priority' => 0, 'file' => 'bar'],
        ];

        $container = $this->createMock(Container::class);
        $container
            ->method('getKnownEntryNames')
            ->willReturn([
                'entryNotFound',
            ]);

        $container
            ->method('extractDefinition')
            ->willReturn(null);

        $this->getDiBuilderMock()
            ->method('build')
            ->willReturn($container);

        $this->getSfContainerBuilderMock()
            ->expects($this->never())
            ->method('addDefinitions');

        $this->expectException(ServiceNotFoundException::class);

        $this->assertInstanceOf(BridgeBuilder::class, $this->buildInstance()
            ->loadDefinition($definitionsFiles)
            ->import('hello', 'world')
            ->initializeSymfonyContainer());
    }

    public function testInitializeSymfonyContainerWithNotSupportedCallableFactory(): void
    {
        $definitionsFiles = [
            ['priority' => 0, 'file' => 'foo'],
            ['priority' => 0, 'file' => 'bar'],
        ];

        $container = $this->createMock(Container::class);
        $container
            ->method('getKnownEntryNames')
            ->willReturn([
                'entryNotSupportedFactory',
            ]);

        $container
            ->method('extractDefinition')
            ->willReturn(
                (new FactoryDefinition(
                    'entryAboutFactoryInvokable',
                    'foo'
                ))
            );

        $this->getDiBuilderMock()
            ->method('build')
            ->willReturn($container);

        $this->getSfContainerBuilderMock()
            ->expects($this->never())
            ->method('addDefinitions');

        $this->expectException(\RuntimeException::class);

        $this->assertInstanceOf(BridgeBuilder::class, $this->buildInstance()
            ->loadDefinition($definitionsFiles)
            ->import('hello', 'world')
            ->initializeSymfonyContainer());
    }

    public function testInitializeSymfonyContainerWithNotSupportedReflectionType(): void
    {
        $definitionsFiles = [
            ['priority' => 0, 'file' => 'foo'],
            ['priority' => 0, 'file' => 'bar'],
        ];

        $container = $this->createMock(Container::class);
        $container
            ->method('getKnownEntryNames')
            ->willReturn([
                'entryNotSupportedFactory',
            ]);

        $container
            ->method('extractDefinition')
            ->willReturn(
                (new FactoryDefinition(
                    'entryAboutFactoryInvokable',
                    function () {}
                ))
            );

        $this->getDiBuilderMock()
            ->method('build')
            ->willReturn($container);

        $this->getSfContainerBuilderMock()
            ->expects($this->never())
            ->method('addDefinitions');

        $this->expectException(\RuntimeException::class);

        $this->assertInstanceOf(BridgeBuilder::class, $this->buildInstance()
            ->loadDefinition($definitionsFiles)
            ->import('hello', 'world')
            ->initializeSymfonyContainer());
    }

    private function prepareForInitializeSymfonyContainerTests(
        array $definitionsFiles,
        ?string $compilationPath,
        bool $enableCache
    ): void {
        $container = $this->createMock(Container::class);
        $container
            ->method('getKnownEntryNames')
            ->willReturn([
                \DateTimeInterface::class,
                \DateTime::class,
                'aliasInPHPDI',
                'aliasInSymfony',
                'entryAboutObject',
                'entryAboutFactoryClosure',
                'entryAboutFactoryInvokable',
                'entryAboutFactoryMethod',
                'entryAboutEnvironment',
                'entryAboutEnvironmentWithDefault',
                'entryAboutString',
                'entryAboutValue',
                'entryAboutArray',
            ]);

        $container->expects($this->exactly(13))
            ->method('extractDefinition')
            ->willReturnMap([
                [\DateTime::class, (new ObjectDefinition(\DateTime::class, \DateTime::class))],
                ['aliasInPHPDI', (new DIReference(\DateTime::class))],
                ['aliasInSymfony', (new DIReference('symfonyService'))],
                ['entryAboutObject', (new ObjectDefinition('entryAboutObject', \stdClass::class))],
                ['entryAboutFactoryClosure', (new FactoryDefinition('entryAboutFactoryClosure', function (): \stdClass {}))],
                [
                    'entryAboutFactoryInvokable',
                    (new FactoryDefinition(
                        'entryAboutFactoryInvokable',
                        new class {
                            public function __invoke(): \stdClass { }
                        }
                    ))
                ],
                [
                    'entryAboutFactoryMethod',
                    (new FactoryDefinition(
                        'entryAboutFactoryMethod',
                        [
                            new class {
                                public function method(): \stdClass { }
                            },
                            'method'
                        ]
                    ))
                ],
                [
                    'entryAboutEnvironment',
                    (new EnvironmentVariableDefinition('ENV_NAME'))
                ],
                [
                    'entryAboutEnvironmentWithDefault',
                    (new EnvironmentVariableDefinition('ENV_NAME', true, 'foo'))
                ],
                [
                    'entryAboutString',
                    (new StringDefinition('stringValue'))
                ],
                [
                    'entryAboutValue',
                    (new ValueDefinition('value'))
                ],
                [
                    'entryAboutArray',
                    (new ArrayDefinition(
                        [
                            'key1' => 'value1',
                            'key2' => [
                                'key3' => 'value2',
                                'key4' => 'value3',
                            ],
                            'key5' => new ArrayDefinition([
                                'key6' => new ArrayDefinition([
                                    'key7' => 'value4',
                                ]),
                            ]),
                        ]
                    ))
                ],
            ]);

        $this->getDiBuilderMock()
            ->method('build')
            ->willReturn($container);

        $this->getSfContainerBuilderMock()
            ->expects($this->once())
            ->method('setAlias')
            ->with('aliasInSymfony', 'symfonyService')
            ->willReturn($this->createMock(Alias::class));

        $this->getSfContainerBuilderMock()
            ->expects($this->exactly(6))
            ->method('setParameter')
            ->willReturnCallback(
                fn (): true => match (func_get_args()) {
                    [
                        'entryAboutEnvironment',
                        '%env(ENV_NAME)%',
                    ] => true,
                    [
                        BridgeBuilder::PREFIX_FOR_DEFAULT_ENV_VALUE . 'entryAboutEnvironmentWithDefault',
                        'foo',
                    ] => true,
                    [
                        'entryAboutEnvironmentWithDefault',
                        '%env(default:' . BridgeBuilder::PREFIX_FOR_DEFAULT_ENV_VALUE . 'entryAboutEnvironmentWithDefault:ENV_NAME)%',
                    ] => true,
                    [
                        'entryAboutString',
                        'stringValue',
                    ] => true,
                    [
                        'entryAboutValue',
                        'value',
                    ] => true,
                    [
                        'entryAboutArray',
                        [
                            'key1' => 'value1',
                            'key2' => [
                                'key3' => 'value2',
                                'key4' => 'value3',
                            ],
                            'key5' => [
                                'key6' => [
                                    'key7' => 'value4',
                                ],
                            ],
                        ],
                    ] => true,
                    default => throw new InvalidArgumentException('Invalid arguments'),
                }
            );

        $this->getSfContainerBuilderMock()
            ->expects($this->once())
            ->method('addDefinitions')
            ->with(
                [
                    DIContainerBuilder::class => new SfDefinition(DIContainerBuilder::class),
                    Bridge::class =>  new SfDefinition(
                        Bridge::class,
                        [
                            new SfReference(DIContainerBuilder::class),
                            new SfReference('service_container'),
                            $definitionsFiles,
                            ['hello' => 'world'],
                            $compilationPath,
                            $enableCache
                        ]
                    ),
                    \DateTimeInterface::class => new SfDefinition(\DateTimeInterface::class)
                        ->setFactory(new SfReference(Bridge::class))
                        ->setArguments([\DateTimeInterface::class])
                        ->setPublic(true),
                    \DateTime::class => new SfDefinition(\DateTime::class)
                        ->setFactory(new SfReference(Bridge::class))
                        ->setArguments([\DateTime::class])
                        ->setPublic(true),
                    'aliasInPHPDI' => new SfDefinition(\DateTime::class)
                        ->setFactory(new SfReference(Bridge::class))
                        ->setArguments(['aliasInPHPDI'])
                        ->setPublic(true),
                    'entryAboutObject' => new SfDefinition(\stdClass::class)
                        ->setFactory(new SfReference(Bridge::class))
                        ->setArguments(['entryAboutObject'])
                        ->setPublic(true),
                    'entryAboutFactoryClosure' => new SfDefinition(\stdClass::class)
                        ->setFactory(new SfReference(Bridge::class))
                        ->setArguments(['entryAboutFactoryClosure'])
                        ->setPublic(true),
                    'entryAboutFactoryInvokable' => new SfDefinition(\stdClass::class)
                        ->setFactory(new SfReference(Bridge::class))
                        ->setArguments(['entryAboutFactoryInvokable'])
                        ->setPublic(true),
                    'entryAboutFactoryMethod' => new SfDefinition(\stdClass::class)
                        ->setFactory(new SfReference(Bridge::class))
                        ->setArguments(['entryAboutFactoryMethod'])
                        ->setPublic(true),
                ]
            );
    }

    public function testInitializeSymfonyContainerWithInvalidParameter(): void
    {
        $container = $this->createMock(Container::class);
        $container
            ->method('getKnownEntryNames')
            ->willReturn([
                'entryAboutObject',
            ]);

        $container->expects($this->exactly(1))
            ->method('extractDefinition')
            ->willReturnMap([
                [
                    'entryAboutObject',
                    (new ValueDefinition(new \stdClass()))
                ],
            ]);

        $this->getDiBuilderMock()
            ->method('build')
            ->willReturn($container);

        $this->getSfContainerBuilderMock()
            ->expects($this->never())
            ->method('setAlias');

        $this->getSfContainerBuilderMock()
            ->expects($this->never())
            ->method('setParameter');

        $this->getSfContainerBuilderMock()
            ->expects($this->never())
            ->method('addDefinitions');

        $this->expectException(InvalidArgumentException::class);
        $this->buildInstance()->initializeSymfonyContainer();
    }

    public function testInitializeSymfonyContainerWithNoCacheAndNoCompilation(): void
    {
        $definitionsFiles = [
            ['priority' => 0, 'file' => 'foo'],
            ['priority' => 0, 'file' => 'bar'],
        ];

        $this->prepareForInitializeSymfonyContainerTests(['foo', 'bar'], null, false);

        $this->assertInstanceOf(BridgeBuilder::class, $this->buildInstance()
            ->loadDefinition($definitionsFiles)
            ->import('hello', 'world')
            ->initializeSymfonyContainer());
    }

    public function testInitializeSymfonyContainerWithCacheAndNoCompilation(): void
    {
        $definitionsFiles = [
            ['priority' => 0, 'file' => 'foo'],
            ['priority' => 0, 'file' => 'bar'],
        ];

        $this->prepareForInitializeSymfonyContainerTests(['foo', 'bar'], null, true);

        $this->assertInstanceOf(BridgeBuilder::class, $this->buildInstance()
            ->loadDefinition($definitionsFiles)
            ->import('hello', 'world')
            ->enableCache(true)
            ->initializeSymfonyContainer());
    }

    public function testInitializeSymfonyContainerWithNoCacheAndCompilation(): void
    {
        $definitionsFiles = [
            ['priority' => 0, 'file' => 'foo'],
            ['priority' => 0, 'file' => 'bar'],
        ];

        $this->prepareForInitializeSymfonyContainerTests(['foo', 'bar'], '/foo/bar', false);

        $this->assertInstanceOf(BridgeBuilder::class, $this->buildInstance()
            ->loadDefinition($definitionsFiles)
            ->import('hello', 'world')
            ->prepareCompilation('/foo/bar')
            ->initializeSymfonyContainer());
    }

    public function testInitializeSymfonyContainerWithNoCacheAndNoCompilationAndOrderedFiles(): void
    {
        $definitionsFiles = [
            ['priority' => 0, 'file' => 'foo'],
            ['priority' => 10, 'file' => 'bar'],
        ];

        $this->prepareForInitializeSymfonyContainerTests(['bar', 'foo'], null, false);

        $this->assertInstanceOf(BridgeBuilder::class, $this->buildInstance()
            ->loadDefinition($definitionsFiles)
            ->import('hello', 'world')
            ->initializeSymfonyContainer());
    }

    public function testInitializeSymfonyContainerWithCacheAndNoCompilationAndOrderedFiles(): void
    {
        $definitionsFiles = [
            ['priority' => 0, 'file' => 'foo'],
            ['priority' => 10, 'file' => 'bar'],
        ];

        $this->prepareForInitializeSymfonyContainerTests(['bar', 'foo'], null, true);

        $this->assertInstanceOf(BridgeBuilder::class, $this->buildInstance()
            ->loadDefinition($definitionsFiles)
            ->import('hello', 'world')
            ->enableCache(true)
            ->initializeSymfonyContainer());
    }

    public function testInitializeSymfonyContainerWithNoCacheAndCompilationAndOrderedFiles(): void
    {
        $definitionsFiles = [
            ['priority' => 0, 'file' => 'foo'],
            ['priority' => 10, 'file' => 'bar'],
        ];

        $this->prepareForInitializeSymfonyContainerTests(['bar', 'foo'], '/foo/bar', false);

        $this->assertInstanceOf(BridgeBuilder::class, $this->buildInstance()
            ->loadDefinition($definitionsFiles)
            ->import('hello', 'world')
            ->prepareCompilation('/foo/bar')
            ->initializeSymfonyContainer());
    }
}
