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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 *
 * @link        http://teknoo.software/di-symfony-bridge Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\Tests\DI\SymfonyBridge\FunctionalTest;

use DI\Definition\Source\SourceCache;
use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\Class1;
use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\Class2;
use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\Class3;

/**
 * Tests interactions between containers, i.e. entries that reference other entries in
 * other containers.
 *
 * @coversNothing
 */
class ContainerInteractionTest extends AbstractFunctionalTest
{
    public function testPhpdiShouldGetEntriesFromSymfonyToConstructAndSymfonyGetInPHPDI()
    {
        //Class 2 is defined in Symfony
        //Class 1 is defined in PHP DI
        //Class 1 requires Class 2
        //So PHPDI requires an entry from Symfony
        //And Symfony Container must use PHPDI to get Class1
        $kernel = $this->createKernel('class2.yml');

        $class1 = $kernel->getContainer()->get(Class1::class);

        self::assertInstanceOf(Class1::class, $class1);
    }

    public function testPhpdiAliasesCanReferenceSymfonyEntries()
    {
        $kernel = $this->createKernel('class2.yml');
        $container = $kernel->getContainer();

        $class2 = $container->get('class2Alias');

        self::assertInstanceOf(Class2::class, $class2);
    }

    public function testPhpdiAliasesCanReferenceSymfonyEntriesFromImport()
    {
        //Class 2 is defined in Symfony
        //Class 3 is defined in PHP DI
        //Class 3 requires Class 2 from alias defined in import 'class2_import'
        //So PHPDI requires an entry from Symfony
        //And Symfony Container must use PHPDI to get Class1
        $kernel = $this->createKernel('class2.yml');

        $class1 = $kernel->getContainer()->get(Class3::class);

        self::assertInstanceOf(Class3::class, $class1);
    }

    public function testPhpdiShouldGetEntriesFromSymfonyToConstructAndSymfonyGetInPHPDIWithCache()
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

        self::assertInstanceOf(Class1::class, $class1);
    }

    public function testPhpdiAliasesCanReferenceSymfonyEntriesWithCache()
    {
        if (!SourceCache::isSupported()) {
            self::markTestSkipped('APCu is not enabled');
        }

        $kernel = $this->createKernel('class2_with_cache.yml');
        $container = $kernel->getContainer();

        $class2 = $container->get('class2Alias');

        self::assertInstanceOf(Class2::class, $class2);
    }

    public function testPhpdiAliasesCanReferenceSymfonyEntriesFromImportWithCache()
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

        self::assertInstanceOf(Class3::class, $class1);
    }

    public function testPhpdiShouldGetEntriesFromSymfonyToConstructAndSymfonyGetInPHPDIWithCompilation()
    {
        //Class 2 is defined in Symfony
        //Class 1 is defined in PHP DI
        //Class 1 requires Class 2
        //So PHPDI requires an entry from Symfony
        //And Symfony Container must use PHPDI to get Class1
        $kernel = $this->createKernel('class2_with_compilation.yml');

        $class1 = $kernel->getContainer()->get(Class1::class);

        self::assertInstanceOf(Class1::class, $class1);
    }

    public function testPhpdiAliasesCanReferenceSymfonyEntriesWithCompilation()
    {
        $kernel = $this->createKernel('class2_with_compilation.yml');
        $container = $kernel->getContainer();

        $class2 = $container->get('class2Alias');

        self::assertInstanceOf(Class2::class, $class2);
    }

    public function testPhpdiAliasesCanReferenceSymfonyEntriesFromImportWithCompilation()
    {
        //Class 2 is defined in Symfony
        //Class 3 is defined in PHP DI
        //Class 3 requires Class 2 from alias defined in import 'class2_import'
        //So PHPDI requires an entry from Symfony
        //And Symfony Container must use PHPDI to get Class1
        $kernel = $this->createKernel('class2_with_compilation.yml');

        $class1 = $kernel->getContainer()->get(Class3::class);

        self::assertInstanceOf(Class3::class, $class1);
    }
}
