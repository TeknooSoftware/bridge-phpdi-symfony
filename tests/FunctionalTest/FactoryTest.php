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
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 *
 * @link        https://teknoo.software/libraries/php-di-symfony-bridge Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\DI\SymfonyBridge\FunctionalTest;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\Product;

/**
 * Checks that every callable form accepted by PHP-DI factories is registered into Symfony with the right class.
 */
#[CoversNothing]
class FactoryTest extends AbstractFunctionalTests
{
    /**
     * @return iterable<string, array{0: string}>
     */
    public static function provideFactoryEntries(): iterable
    {
        yield 'closure' => ['product.closure'];
        yield 'class and method' => ['product.class_method'];
        yield 'class and static method' => ['product.class_static'];
        yield 'Class::staticMethod string' => ['product.string_static'];
        yield 'Class::method string' => ['product.string_method'];
        yield 'invokable class name' => ['product.invokable_class'];
        yield 'invokable object' => ['product.invokable_object'];
    }

    #[DataProvider('provideFactoryEntries')]
    public function testSymfonyResolvesEntriesBuiltByPhpDiFactories(string $entry): void
    {
        $kernel = $this->createKernel('factories.yml');
        $container = $kernel->getContainer();

        $this->assertTrue($container->has($entry));
        $this->assertInstanceOf(Product::class, $container->get($entry));
    }
}
