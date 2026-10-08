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
use Closure;
use Psr\Container\ContainerInterface;
use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\ArrayConsumer;
use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\Class2;
use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\Product;

/**
 * Checks how PHP-DI values, strings, arrays and environment variables are exported as Symfony parameters.
 */
#[CoversNothing]
class ParameterTest extends AbstractFunctionalTests
{
    public function testPercentSignsInPhpDiValuesAreKeptLiteralInSymfony(): void
    {
        $kernel = $this->createKernel('percent.yml');
        $container = $kernel->getContainer();

        $this->assertSame('a%b%c', $container->getParameter('percent.value'));
        $this->assertSame('100%%', $container->getParameter('percent.escaped'));
        $this->assertSame(['k' => 'x%y%', 'n' => ['z%']], $container->getParameter('percent.array'));
        $this->assertSame('d%e%f', $container->getParameter('percent.env'));
    }

    public function testPhpDiStringExpressionsAreResolvedByPhpDiAndKeptLiteralInSymfony(): void
    {
        $kernel = $this->createKernel('percent.yml');
        $container = $kernel->getContainer();

        //Symfony knows only the raw expression
        $this->assertSame('{percent.value}/d', $container->getParameter('percent.string'));

        //PHP-DI resolves it, through the bridge
        $bridge = $container->get(ContainerInterface::class);
        $this->assertSame('a%b%c/d', $bridge->get('percent.string'));
    }

    public function testPhpDiObjectValuesAreRegisteredAsSymfonyServices(): void
    {
        $kernel = $this->createKernel('values.yml');
        $container = $kernel->getContainer();

        $this->assertTrue($container->has('value.object'));
        $this->assertInstanceOf(Product::class, $container->get('value.object'));
        $this->assertSame($container->get('value.object'), $container->get('value.object'));

        $this->assertTrue($container->has('value.closure'));
        $this->assertInstanceOf(Closure::class, $container->get('value.closure'));

        $this->assertFalse($container->has('value.scalar'));
        $this->assertSame(42, $container->getParameter('value.scalar'));
    }

    public function testPhpDiArraysContainingDefinitionsAreInjectableIntoSymfonyServices(): void
    {
        $kernel = $this->createKernel('array_refs.yml');
        $container = $kernel->getContainer();

        //Symfony's Container::get() can only return objects, these entries are private services, only injectable
        $this->assertFalse($container->has('array.handlers'));
        $this->assertFalse($container->has('array.nested'));

        $consumer = $container->get('array.consumer');
        $this->assertInstanceOf(ArrayConsumer::class, $consumer);
        $this->assertCount(2, $consumer->handlers);
        $this->assertInstanceOf(Class2::class, $consumer->handlers[0]);
        $this->assertSame($container->get('class2'), $consumer->handlers[0]);
        $this->assertInstanceOf(Product::class, $consumer->handlers[1]);
        $this->assertInstanceOf(Class2::class, $consumer->nested['level1']['level2']);

        $this->assertFalse($container->has('array.plain'));
        $this->assertSame(['a' => 1, 'b' => ['c' => 'd']], $container->getParameter('array.plain'));
    }
}
