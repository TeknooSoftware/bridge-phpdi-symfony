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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Teknoo\DI\SymfonyBridge\DependencyInjection\Configuration;

#[CoversClass(Configuration::class)]
class ConfigurationTest extends TestCase
{
    public function buildInstance(): Configuration
    {
        return new Configuration();
    }

    public function testGetConfigTreeBuilder(): void
    {
        $treeBuilder = $this->buildInstance()->getConfigTreeBuilder();

        $this->assertInstanceOf(TreeBuilder::class, $treeBuilder);
    }

    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    private function process(array $config): array
    {
        return new Processor()->processConfiguration($this->buildInstance(), [$config]);
    }

    public function testDefaultValues(): void
    {
        $this->assertEquals(
            [
                'compilation_path' => null,
                'enable_cache' => false,
                'definitions' => [],
                'import' => [],
                'extensions' => [],
            ],
            $this->process([]),
        );
    }

    public function testDefinitionsAndExtensionsAreNormalizedFromStrings(): void
    {
        $processed = $this->process([
            'definitions' => ['/foo/di.php', ['priority' => 10, 'file' => '/bar/di.php']],
            'extensions' => ['app.ext', ['priority' => 5, 'name' => 'App\\Ext']],
            'import' => ['di_key' => 'sf_key'],
        ]);

        $this->assertEquals(
            [
                ['priority' => 0, 'file' => '/foo/di.php'],
                ['priority' => 10, 'file' => '/bar/di.php'],
            ],
            $processed['definitions'],
        );
        $this->assertEquals(
            [
                ['priority' => 0, 'name' => 'app.ext'],
                ['priority' => 5, 'name' => 'App\\Ext'],
            ],
            $processed['extensions'],
        );
        $this->assertEquals(['di_key' => 'sf_key'], $processed['import']);
    }

    /**
     * @return iterable<string, array{0: array<string, mixed>}>
     */
    public static function provideInvalidConfigurations(): iterable
    {
        yield 'definition without file' => [['definitions' => [['priority' => 1]]]];
        yield 'definition with empty file' => [['definitions' => [['priority' => 1, 'file' => '']]]];
        yield 'definition with null file' => [['definitions' => [['file' => null]]]];
        yield 'extension without name' => [['extensions' => [['priority' => 1]]]];
        yield 'extension with empty name' => [['extensions' => [['name' => '']]]];
    }

    /**
     * @param array<string, mixed> $config
     */
    #[DataProvider('provideInvalidConfigurations')]
    public function testFileAndNameKeysAreRequired(array $config): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->process($config);
    }
}
