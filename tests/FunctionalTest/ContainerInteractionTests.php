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

use DI\Definition\Source\SourceCache;
use PHPUnit\Framework\Attributes\CoversNothing;
use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\Class1;
use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\Class2;
use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\Class3;

/**
 * Tests interactions between containers, i.e. entries that reference other entries in
 * other containers.
 *
 */
#[CoversNothing]
class ContainerInteractionTests extends AbstractFunctionalTests
{
    public function testPhpdiShouldGetEntriesFromSymfonyToConstructAndSymfonyGetInPHPDI(): void
    {
        //Class 2 is defined in Symfony
        //Class 1 is defined in PHP DI
        //Class 1 requires Class 2
        //So PHPDI requires an entry from Symfony
        //And Symfony Container must use PHPDI to get Class1
        $kernel = $this->createKernel('class2.yml');

        $class1 = $kernel->getContainer()->get(Class1::class);

        $this->assertInstanceOf(Class1::class, $class1);
    }

    public function testPhpdiAliasesCanReferenceSymfonyEntries(): void
    {
        $kernel = $this->createKernel('class2.yml');
        $container = $kernel->getContainer();

        $class2 = $container->get('class2Alias');

        $this->assertInstanceOf(Class2::class, $class2);
    }

    public function testPhpdiAliasesCanReferenceSymfonyEntriesFromImport(): void
    {
        //Class 2 is defined in Symfony
        //Class 3 is defined in PHP DI
        //Class 3 requires Class 2 from alias defined in import 'class2_import'
        //So PHPDI requires an entry from Symfony
        //And Symfony Container must use PHPDI to get Class1
        $kernel = $this->createKernel('class2.yml');

        $class1 = $kernel->getContainer()->get(Class3::class);

        $this->assertInstanceOf(Class3::class, $class1);
    }

    public function testPhpdiShouldGetEntriesFromSymfonyToConstructAndSymfonyGetInPHPDIWithCache(): void
    {
        if (!SourceCache::isSupported()) {
            self::markTestSkipped('APCu is not enabled');
        }

        //Class 2 is defined in Symfony
        //Class 1 is defined in PHP DI
        //Class 1 requires Class 2
        //So PHPDI requires an entry from Symfony
        //And Symfony Container must use PHPDI to get Class1
        $kernel = $this->createKernel('class2_with_cache.yml');

        $class1 = $kernel->getContainer()->get(Class1::class);

        $this->assertInstanceOf(Class1::class, $class1);
    }

    public function testPhpdiAliasesCanReferenceSymfonyEntriesWithCache(): void
    {
        if (!SourceCache::isSupported()) {
            self::markTestSkipped('APCu is not enabled');
        }

        $kernel = $this->createKernel('class2_with_cache.yml');
        $container = $kernel->getContainer();

        $class2 = $container->get('class2Alias');

        $this->assertInstanceOf(Class2::class, $class2);
    }

    public function testPhpdiAliasesCanReferenceSymfonyEntriesFromImportWithCache(): void
    {
        if (!SourceCache::isSupported()) {
            self::markTestSkipped('APCu is not enabled');
        }

        //Class 2 is defined in Symfony
        //Class 3 is defined in PHP DI
        //Class 3 requires Class 2 from alias defined in import 'class2_import'
        //So PHPDI requires an entry from Symfony
        //And Symfony Container must use PHPDI to get Class1
        $kernel = $this->createKernel('class2_with_cache.yml');

        $class1 = $kernel->getContainer()->get(Class3::class);

        $this->assertInstanceOf(Class3::class, $class1);
    }

    public function testPhpdiShouldGetEntriesFromSymfonyToConstructAndSymfonyGetInPHPDIWithCompilation(): void
    {
        //Class 2 is defined in Symfony
        //Class 1 is defined in PHP DI
        //Class 1 requires Class 2
        //So PHPDI requires an entry from Symfony
        //And Symfony Container must use PHPDI to get Class1
        $kernel = $this->createKernel('class2_with_compilation.yml');

        $class1 = $kernel->getContainer()->get(Class1::class);

        $this->assertInstanceOf(Class1::class, $class1);
    }

    public function testPhpdiAliasesCanReferenceSymfonyEntriesWithCompilation(): void
    {
        $kernel = $this->createKernel('class2_with_compilation.yml');
        $container = $kernel->getContainer();

        $class2 = $container->get('class2Alias');

        $this->assertInstanceOf(Class2::class, $class2);
    }

    public function testPhpdiAliasesCanReferenceSymfonyEntriesFromImportWithCompilation(): void
    {
        //Class 2 is defined in Symfony
        //Class 3 is defined in PHP DI
        //Class 3 requires Class 2 from alias defined in import 'class2_import'
        //So PHPDI requires an entry from Symfony
        //And Symfony Container must use PHPDI to get Class1
        $kernel = $this->createKernel('class2_with_compilation.yml');

        $class1 = $kernel->getContainer()->get(Class3::class);

        $this->assertInstanceOf(Class3::class, $class1);
    }
}
