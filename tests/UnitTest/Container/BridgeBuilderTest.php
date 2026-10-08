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
use DI\Definition\Definition as DIDefinition;
use DI\Definition\EnvironmentVariableDefinition;
use DI\Definition\FactoryDefinition;
use DI\Definition\ObjectDefinition;
use DI\Definition\Reference as DIReference;
use DI\Definition\StringDefinition;
use DI\Definition\ValueDefinition;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder as SfContainerBuilder;
use Symfony\Component\DependencyInjection\Definition as SfDefinition;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference as SfReference;
use Teknoo\DI\SymfonyBridge\Container\Bridge;
use Teknoo\DI\SymfonyBridge\Container\BridgeBuilder;
use Teknoo\DI\SymfonyBridge\Container\Container;
use Teknoo\Tests\DI\SymfonyBridge\UnitTest\Container\Support\EnumFixture;
use Teknoo\Tests\DI\SymfonyBridge\UnitTest\Container\Support\FactoryFixture;
use Teknoo\Tests\DI\SymfonyBridge\UnitTest\Container\Support\InvokableFixture;

use function fopen;
use function func_get_args;
use function json_encode;

#[CoversClass(BridgeBuilder::class)]
class BridgeBuilderTest extends TestCase
{
    private ?DIContainerBuilder $diBuilder = null;

    private ?SfContainerBuilder $sfContainer = null;

    private function getDiBuilderStub(): DIContainerBuilder&Stub
    {
        if (!$this->diBuilder instanceof DIContainerBuilder) {
            $this->diBuilder = $this->createStub(DIContainerBuilder::class);
        }

        return $this->diBuilder;
    }

    private function getSfContainerBuilderStub(): SfContainerBuilder&Stub
    {
        if (!$this->sfContainer instanceof SfContainerBuilder) {
            $this->sfContainer = $this->createStub(SfContainerBuilder::class);
        }

        return $this->sfContainer;
    }

    public function buildInstance(): BridgeBuilder
    {
        return new BridgeBuilder(
            $this->getDiBuilderStub(),
            $this->getSfContainerBuilderStub()
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

        $container = $this->createStub(DIContainer::class);

        $this->getDiBuilderStub()
            ->method('build')
            ->willReturn($container);

        $this->buildInstance()->initializeSymfonyContainer();
    }

    public function testInitializeSymfonyContainerWithNotFoundEntry(): void
    {
        $this->sfContainer = $this->createMock(SfContainerBuilder::class);
        $definitionsFiles = [
            ['priority' => 0, 'file' => 'foo'],
            ['priority' => 0, 'file' => 'bar'],
        ];

        $container = $this->createStub(Container::class);
        $container
            ->method('getKnownEntryNames')
            ->willReturn([
                'entryNotFound',
            ]);

        $container
            ->method('extractDefinition')
            ->willReturn(null);

        $this->getDiBuilderStub()
            ->method('build')
            ->willReturn($container);

        $this->getSfContainerBuilderStub()
            ->expects($this->never())
            ->method('addDefinitions');

        $this->expectException(ServiceNotFoundException::class);

        $this->assertInstanceOf(BridgeBuilder::class, $this->buildInstance()
            ->loadDefinition($definitionsFiles)
            ->import('hello', 'world')
            ->initializeSymfonyContainer());
    }

    public function testInitializeSymfonyContainerWithCircularReferences(): void
    {
        $this->sfContainer = $this->createMock(SfContainerBuilder::class);

        $container = $this->createStub(Container::class);
        $container
            ->method('getKnownEntryNames')
            ->willReturn(['entryA']);

        $container
            ->method('extractDefinition')
            ->willReturnMap([
                ['entryA', new DIReference('entryB')],
                ['entryB', new DIReference('entryC')],
                ['entryC', new DIReference('entryA')],
            ]);

        $this->getDiBuilderStub()
            ->method('build')
            ->willReturn($container);

        $this->getSfContainerBuilderStub()
            ->expects($this->never())
            ->method('addDefinitions');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Circular reference detected for 'entryA' (entryA -> entryB -> entryC -> entryA)");

        $this->buildInstance()
            ->loadDefinition([['priority' => 0, 'file' => 'foo']])
            ->initializeSymfonyContainer();
    }

    public function testInitializeSymfonyContainerWithNotSupportedCallableFactory(): void
    {
        $this->sfContainer = $this->createMock(SfContainerBuilder::class);
        $definitionsFiles = [
            ['priority' => 0, 'file' => 'foo'],
            ['priority' => 0, 'file' => 'bar'],
        ];

        $container = $this->createStub(Container::class);
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

        $this->getDiBuilderStub()
            ->method('build')
            ->willReturn($container);

        $this->getSfContainerBuilderStub()
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
        $this->sfContainer = $this->createMock(SfContainerBuilder::class);
        $definitionsFiles = [
            ['priority' => 0, 'file' => 'foo'],
            ['priority' => 0, 'file' => 'bar'],
        ];

        $container = $this->createStub(Container::class);
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

        $this->getDiBuilderStub()
            ->method('build')
            ->willReturn($container);

        $this->getSfContainerBuilderStub()
            ->expects($this->never())
            ->method('addDefinitions');

        $this->expectException(\RuntimeException::class);

        $this->assertInstanceOf(BridgeBuilder::class, $this->buildInstance()
            ->loadDefinition($definitionsFiles)
            ->import('hello', 'world')
            ->initializeSymfonyContainer());
    }

    /**
     * @return iterable<string, array{0: callable|array|string, 1: string}>
     */
    public static function provideSupportedFactoryCallables(): iterable
    {
        yield 'closure' => [fn (): \stdClass => new \stdClass(), \stdClass::class];
        yield 'closure with static return type' => [
            \Closure::bind(fn (): static => $this, new FactoryFixture(), FactoryFixture::class),
            FactoryFixture::class,
        ];
        yield 'invokable object' => [new InvokableFixture(), \stdClass::class];
        yield 'object and method' => [[new FactoryFixture(), 'create'], \stdClass::class];
        yield 'class name and instance method' => [[FactoryFixture::class, 'create'], \stdClass::class];
        yield 'class name and static method' => [[FactoryFixture::class, 'createStatic'], \stdClass::class];
        yield 'class::staticMethod string' => [FactoryFixture::class . '::createStatic', \stdClass::class];
        yield 'class::instanceMethod string' => [FactoryFixture::class . '::create', \stdClass::class];
        yield 'invokable class name' => [InvokableFixture::class, \stdClass::class];
        yield 'function name' => ['DI\\value', ValueDefinition::class];
        yield 'self return type' => [[FactoryFixture::class, 'createSelf'], FactoryFixture::class];
        yield 'static return type' => [[FactoryFixture::class, 'createStatic2'], FactoryFixture::class];
    }

    /**
     * @param callable|array|string $callable
     */
    #[DataProvider('provideSupportedFactoryCallables')]
    public function testInitializeSymfonyContainerWithSupportedFactoryCallables(
        callable|array|string $callable,
        string $expectedClass,
    ): void {
        $this->sfContainer = $this->createMock(SfContainerBuilder::class);

        $container = $this->createStub(Container::class);
        $container
            ->method('getKnownEntryNames')
            ->willReturn(['entryFactory']);

        $container
            ->method('extractDefinition')
            ->willReturn(new FactoryDefinition('entryFactory', $callable));

        $this->getDiBuilderStub()
            ->method('build')
            ->willReturn($container);

        $this->getSfContainerBuilderStub()
            ->expects($this->once())
            ->method('addDefinitions')
            ->with(
                [
                    DIContainerBuilder::class => new SfDefinition(DIContainerBuilder::class),
                    Bridge::class => new SfDefinition(
                        Bridge::class,
                        [
                            new SfReference(DIContainerBuilder::class),
                            new SfReference('service_container'),
                            [],
                            [],
                            null,
                            false
                        ]
                    ),
                    'entryFactory' => new SfDefinition($expectedClass)
                        ->setFactory(new SfReference(Bridge::class))
                        ->setArguments(['entryFactory'])
                        ->setPublic(true),
                ]
            );

        $this->assertInstanceOf(BridgeBuilder::class, $this->buildInstance()->initializeSymfonyContainer());
    }

    /**
     * @return iterable<string, array{0: callable|array|string, 1: string}>
     */
    public static function provideUnsupportedFactoryCallables(): iterable
    {
        yield 'unknown class and method' => [['NotAnExistingClass', 'create'], 'Callable not supported'];
        yield 'unknown method' => [[FactoryFixture::class, 'unknownMethod'], 'Invalid callable'];
        yield 'unknown class::method string' => ['NotAnExistingClass::create', 'Invalid callable'];
        yield 'unknown function' => ['not_an_existing_function', 'Callable not supported'];
        yield 'not invokable class' => [FactoryFixture::class, 'Callable not supported'];
        yield 'array with too many items' => [[FactoryFixture::class, 'create', 'foo'], 'Callable not supported'];
        yield 'missing return type' => [[FactoryFixture::class, 'withoutReturnType'], 'Missing a return type'];
    }

    /**
     * @param callable|array|string $callable
     */
    #[DataProvider('provideUnsupportedFactoryCallables')]
    public function testInitializeSymfonyContainerWithUnsupportedFactoryCallables(
        callable|array|string $callable,
        string $expectedMessage,
    ): void {
        $this->sfContainer = $this->createMock(SfContainerBuilder::class);

        $container = $this->createStub(Container::class);
        $container
            ->method('getKnownEntryNames')
            ->willReturn(['entryFactory']);

        $container
            ->method('extractDefinition')
            ->willReturn(new FactoryDefinition('entryFactory', $callable));

        $this->getDiBuilderStub()
            ->method('build')
            ->willReturn($container);

        $this->getSfContainerBuilderStub()
            ->expects($this->never())
            ->method('addDefinitions');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->buildInstance()->initializeSymfonyContainer();
    }

    public function testInitializeSymfonyContainerEscapesPercentSignsInParameters(): void
    {
        $this->sfContainer = $this->createMock(SfContainerBuilder::class);

        $container = $this->createStub(Container::class);
        $container
            ->method('getKnownEntryNames')
            ->willReturn(['entryString', 'entryValue', 'entryArray', 'entryEnv', 'entryEnum']);

        $container
            ->method('extractDefinition')
            ->willReturnMap([
                ['entryString', new StringDefinition('a%b%c')],
                ['entryValue', new ValueDefinition('100%%')],
                ['entryArray', new ArrayDefinition(['k' => 'x%y%', 'n' => new ArrayDefinition(['z%'])])],
                ['entryEnv', new EnvironmentVariableDefinition('ENV_NAME', true, 'd%e%f')],
                ['entryEnum', new ValueDefinition(EnumFixture::Foo)],
            ]);

        $this->getDiBuilderStub()
            ->method('build')
            ->willReturn($container);

        $this->getSfContainerBuilderStub()
            ->expects($this->exactly(6))
            ->method('setParameter')
            ->willReturnCallback(
                fn (): true => match (func_get_args()) {
                    ['entryString', 'a%%b%%c'] => true,
                    ['entryValue', '100%%%%'] => true,
                    ['entryArray', ['k' => 'x%%y%%', 'n' => ['z%%']]] => true,
                    [BridgeBuilder::PREFIX_FOR_DEFAULT_ENV_VALUE . 'entryEnv', 'd%%e%%f'] => true,
                    [
                        'entryEnv',
                        '%env(default:' . BridgeBuilder::PREFIX_FOR_DEFAULT_ENV_VALUE . 'entryEnv:ENV_NAME)%',
                    ] => true,
                    ['entryEnum', EnumFixture::Foo] => true,
                    default => throw new InvalidArgumentException('Invalid arguments ' . json_encode(func_get_args())),
                }
            );

        $this->getSfContainerBuilderStub()
            ->expects($this->once())
            ->method('addDefinitions');

        $this->assertInstanceOf(BridgeBuilder::class, $this->buildInstance()->initializeSymfonyContainer());
    }

    public function testInitializeSymfonyContainerWithArraysContainingDefinitionsRegisteredAsServices(): void
    {
        $this->sfContainer = $this->createMock(SfContainerBuilder::class);
        $container = $this->createStub(Container::class);
        $container
            ->method('getKnownEntryNames')
            ->willReturn([
                'entryArrayWithReferences',
                'entryArrayWithNestedObject',
                'entryValueArrayWithObject',
                'entryPlainArray',
            ]);

        $container
            ->method('extractDefinition')
            ->willReturnMap([
                [
                    'entryArrayWithReferences',
                    new ArrayDefinition(['a' => new DIReference('x'), 'b' => 'scalar']),
                ],
                [
                    'entryArrayWithNestedObject',
                    new ArrayDefinition([
                        'n' => new ArrayDefinition([new ObjectDefinition('o', \stdClass::class)]),
                    ]),
                ],
                ['entryValueArrayWithObject', new ValueDefinition(['k' => [new \stdClass()]])],
                ['entryPlainArray', new ArrayDefinition(['k' => 1, 'e' => EnumFixture::Bar, 'n' => ['x']])],
            ]);

        $this->getDiBuilderStub()
            ->method('build')
            ->willReturn($container);

        $this->getSfContainerBuilderStub()
            ->expects($this->once())
            ->method('setParameter')
            ->with('entryPlainArray', ['k' => 1, 'e' => EnumFixture::Bar, 'n' => ['x']]);

        $this->getSfContainerBuilderStub()
            ->expects($this->once())
            ->method('addDefinitions')
            ->with(
                [
                    DIContainerBuilder::class => new SfDefinition(DIContainerBuilder::class),
                    Bridge::class => new SfDefinition(
                        Bridge::class,
                        [
                            new SfReference(DIContainerBuilder::class),
                            new SfReference('service_container'),
                            [],
                            [],
                            null,
                            false
                        ]
                    ),
                    'entryArrayWithReferences' => new SfDefinition('array')
                        ->setFactory(new SfReference(Bridge::class))
                        ->setArguments(['entryArrayWithReferences'])
                        ->setPublic(false),
                    'entryArrayWithNestedObject' => new SfDefinition('array')
                        ->setFactory(new SfReference(Bridge::class))
                        ->setArguments(['entryArrayWithNestedObject'])
                        ->setPublic(false),
                    'entryValueArrayWithObject' => new SfDefinition('array')
                        ->setFactory(new SfReference(Bridge::class))
                        ->setArguments(['entryValueArrayWithObject'])
                        ->setPublic(false),
                ]
            );

        $this->assertInstanceOf(BridgeBuilder::class, $this->buildInstance()->initializeSymfonyContainer());
    }

    public function testInitializeSymfonyContainerWithEnvironmentDefaultValueReferencingParameters(): void
    {
        $this->sfContainer = $this->createMock(SfContainerBuilder::class);
        $container = $this->createStub(Container::class);
        $container
            ->method('getKnownEntryNames')
            ->willReturn(['entryEnvToValue', 'entryEnvToString', 'entryEnvToSymfonyParameter', 'entryEnvToArray']);

        $container
            ->method('extractDefinition')
            ->willReturnMap([
                ['entryEnvToValue', new EnvironmentVariableDefinition('ENV_A', true, new DIReference('aValue'))],
                ['aValue', new ValueDefinition('foo')],
                ['entryEnvToString', new EnvironmentVariableDefinition('ENV_B', true, new DIReference('aString'))],
                ['aString', new DIReference('anotherString')],
                ['anotherString', new StringDefinition('bar')],
                [
                    'entryEnvToSymfonyParameter',
                    new EnvironmentVariableDefinition('ENV_C', true, new DIReference('kernel.environment')),
                ],
                ['kernel.environment', null],
                ['entryEnvToArray', new EnvironmentVariableDefinition('ENV_D', true, new DIReference('anArray'))],
                ['anArray', new ArrayDefinition(['a' => 1])],
            ]);

        $this->getDiBuilderStub()
            ->method('build')
            ->willReturn($container);

        $this->getSfContainerBuilderStub()
            ->method('hasParameter')
            ->willReturnMap([['kernel.environment', true]]);

        $this->getSfContainerBuilderStub()
            ->expects($this->exactly(4))
            ->method('setParameter')
            ->willReturnCallback(
                fn (): true => match (func_get_args()) {
                    ['entryEnvToValue', '%env(default:aValue:ENV_A)%'] => true,
                    ['entryEnvToString', '%env(default:aString:ENV_B)%'] => true,
                    ['entryEnvToSymfonyParameter', '%env(default:kernel.environment:ENV_C)%'] => true,
                    ['entryEnvToArray', '%env(default:anArray:ENV_D)%'] => true,
                    default => throw new InvalidArgumentException('Invalid arguments ' . json_encode(func_get_args())),
                }
            );

        $this->getSfContainerBuilderStub()
            ->expects($this->once())
            ->method('addDefinitions');

        $this->assertInstanceOf(BridgeBuilder::class, $this->buildInstance()->initializeSymfonyContainer());
    }

    /**
     * @return iterable<string, array{0: mixed, 1: ?DIDefinition}>
     */
    public static function provideInvalidEnvironmentDefaultValues(): iterable
    {
        yield 'reference to an object' => [new DIReference('target'), new ObjectDefinition('target', \stdClass::class)];
        yield 'reference to a factory' => [
            new DIReference('target'),
            new FactoryDefinition('target', fn (): \stdClass => new \stdClass()),
        ];
        yield 'reference to an array with objects' => [
            new DIReference('target'),
            new ArrayDefinition([new DIReference('foo')]),
        ];
        yield 'reference to an object value' => [new DIReference('target'), new ValueDefinition(new \stdClass())];
        yield 'reference to an unknown entry' => [new DIReference('target'), null];
        yield 'nested object definition' => [new ObjectDefinition('target', \stdClass::class), null];
    }

    #[DataProvider('provideInvalidEnvironmentDefaultValues')]
    public function testInitializeSymfonyContainerWithInvalidEnvironmentDefaultValue(
        mixed $defaultValue,
        ?DIDefinition $targetDefinition,
    ): void {
        $this->sfContainer = $this->createMock(SfContainerBuilder::class);
        $container = $this->createStub(Container::class);
        $container
            ->method('getKnownEntryNames')
            ->willReturn(['entryEnv']);

        $container
            ->method('extractDefinition')
            ->willReturnMap([
                ['entryEnv', new EnvironmentVariableDefinition('ENV_A', true, $defaultValue)],
                ['target', $targetDefinition],
            ]);

        $this->getDiBuilderStub()
            ->method('build')
            ->willReturn($container);

        $this->getSfContainerBuilderStub()
            ->expects($this->never())
            ->method('setParameter');

        $this->getSfContainerBuilderStub()
            ->expects($this->never())
            ->method('addDefinitions');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("default value of the environment variable 'ENV_A' for 'entryEnv'");

        $this->buildInstance()->initializeSymfonyContainer();
    }

    public function testInitializeSymfonyContainerIgnoresPhpDiInternalEntries(): void
    {
        $this->sfContainer = $this->createMock(SfContainerBuilder::class);
        $container = $this->createMock(Container::class);
        $container
            ->method('getKnownEntryNames')
            ->willReturn([
                \Psr\Container\ContainerInterface::class,
                DIContainer::class,
                \DI\FactoryInterface::class,
                \Invoker\InvokerInterface::class,
                \DateTime::class,
            ]);

        $container->expects($this->never())
            ->method('extractDefinition');

        $this->getDiBuilderStub()
            ->method('build')
            ->willReturn($container);

        $this->getSfContainerBuilderStub()
            ->expects($this->once())
            ->method('addDefinitions')
            ->with(
                [
                    DIContainerBuilder::class => new SfDefinition(DIContainerBuilder::class),
                    Bridge::class => new SfDefinition(
                        Bridge::class,
                        [
                            new SfReference(DIContainerBuilder::class),
                            new SfReference('service_container'),
                            [],
                            [],
                            null,
                            false
                        ]
                    ),
                    \DateTime::class => new SfDefinition(\DateTime::class)
                        ->setFactory(new SfReference(Bridge::class))
                        ->setArguments([\DateTime::class])
                        ->setPublic(true),
                ]
            );

        $this->assertInstanceOf(BridgeBuilder::class, $this->buildInstance()->initializeSymfonyContainer());
    }

    public function testDefinitionsFilesAreOrderedByPriorityAndDeduplicated(): void
    {
        $this->sfContainer = $this->createMock(SfContainerBuilder::class);
        $container = $this->createStub(Container::class);
        $container
            ->method('getKnownEntryNames')
            ->willReturn([]);

        $this->getDiBuilderStub()
            ->method('build')
            ->willReturn($container);

        $this->getSfContainerBuilderStub()
            ->expects($this->once())
            ->method('addDefinitions')
            ->with(
                [
                    DIContainerBuilder::class => new SfDefinition(DIContainerBuilder::class),
                    Bridge::class => new SfDefinition(
                        Bridge::class,
                        [
                            new SfReference(DIContainerBuilder::class),
                            new SfReference('service_container'),
                            ['high', 'foo', 'bar', 'low'],
                            [],
                            null,
                            false
                        ]
                    ),
                ]
            );

        $this->assertInstanceOf(BridgeBuilder::class, $this->buildInstance()
            ->loadDefinition([
                ['priority' => 0, 'file' => 'foo'],
                ['file' => 'bar'],
                ['priority' => -5, 'file' => 'low'],
            ])
            ->loadDefinition([
                ['priority' => 10, 'file' => 'high'],
                //Duplicated file, the last declaration wins but the position is kept
                ['priority' => 0, 'file' => 'foo'],
            ])
            ->initializeSymfonyContainer());
    }

    private function prepareForInitializeSymfonyContainerTests(
        array $definitionsFiles,
        ?string $compilationPath,
        bool $enableCache
    ): void {
        $this->sfContainer = $this->createMock(SfContainerBuilder::class);
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

        $this->getDiBuilderStub()
            ->method('build')
            ->willReturn($container);

        $this->getSfContainerBuilderStub()
            ->expects($this->once())
            ->method('setAlias')
            ->with('aliasInSymfony', 'symfonyService')
            ->willReturn($this->createStub(Alias::class));

        $this->getSfContainerBuilderStub()
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

        $this->getSfContainerBuilderStub()
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
        $this->sfContainer = $this->createMock(SfContainerBuilder::class);
        $container = $this->createMock(Container::class);
        $container
            ->method('getKnownEntryNames')
            ->willReturn([
                'entryAboutResource',
            ]);

        $container->expects($this->exactly(1))
            ->method('extractDefinition')
            ->willReturnMap([
                [
                    'entryAboutResource',
                    (new ValueDefinition(fopen('php://memory', 'r')))
                ],
            ]);

        $this->getDiBuilderStub()
            ->method('build')
            ->willReturn($container);

        $this->getSfContainerBuilderStub()
            ->expects($this->never())
            ->method('setAlias');

        $this->getSfContainerBuilderStub()
            ->expects($this->never())
            ->method('setParameter');

        $this->getSfContainerBuilderStub()
            ->expects($this->never())
            ->method('addDefinitions');

        $this->expectException(InvalidArgumentException::class);
        $this->buildInstance()->initializeSymfonyContainer();
    }

    public function testInitializeSymfonyContainerWithObjectValuesRegisteredAsServices(): void
    {
        $this->sfContainer = $this->createMock(SfContainerBuilder::class);
        $container = $this->createStub(Container::class);
        $container
            ->method('getKnownEntryNames')
            ->willReturn([
                'entryAboutObject',
                'entryAboutClosure',
            ]);

        $container
            ->method('extractDefinition')
            ->willReturnMap([
                ['entryAboutObject', new ValueDefinition(new \stdClass())],
                ['entryAboutClosure', new ValueDefinition(fn (): \stdClass => new \stdClass())],
            ]);

        $this->getDiBuilderStub()
            ->method('build')
            ->willReturn($container);

        $this->getSfContainerBuilderStub()
            ->expects($this->never())
            ->method('setParameter');

        $this->getSfContainerBuilderStub()
            ->expects($this->once())
            ->method('addDefinitions')
            ->with(
                [
                    DIContainerBuilder::class => new SfDefinition(DIContainerBuilder::class),
                    Bridge::class => new SfDefinition(
                        Bridge::class,
                        [
                            new SfReference(DIContainerBuilder::class),
                            new SfReference('service_container'),
                            [],
                            [],
                            null,
                            false
                        ]
                    ),
                    'entryAboutObject' => new SfDefinition(\stdClass::class)
                        ->setFactory(new SfReference(Bridge::class))
                        ->setArguments(['entryAboutObject'])
                        ->setPublic(true),
                    'entryAboutClosure' => new SfDefinition(\Closure::class)
                        ->setFactory(new SfReference(Bridge::class))
                        ->setArguments(['entryAboutClosure'])
                        ->setPublic(true),
                ]
            );

        $this->assertInstanceOf(BridgeBuilder::class, $this->buildInstance()->initializeSymfonyContainer());
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
