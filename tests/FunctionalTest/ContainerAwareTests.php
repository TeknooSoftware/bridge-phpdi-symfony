<?php

/*
 * Symfony Bridge.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 *
 * @link        http://teknoo.software/bridge-phpdi-symfony Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\DI\SymfonyBridge\FunctionalTest;

use DI\Definition\Source\SourceCache;
use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\ContainerAwareController;

/**
 * @coversNothing
 */
class ContainerAwareTests extends AbstractFunctionalTests
{
    /**
     * @link https://github.com/PHP-DI/Symfony-Bridge/issues/2
     */
    public function testContainerAwareWithoutCacheAndWithoutCompilation()
    {
        $kernel = $this->createKernel('empty.yml');
        $container = $kernel->getContainer();

        /** @var ContainerAwareController $class */
        $class = $container->get(ContainerAwareController::class);

        self::assertSame($container, $class->container);
    }

    /**
     * @link https://github.com/PHP-DI/Symfony-Bridge/issues/2
     */
    public function testContainerAwareWithCacheAndWithoutCompilation()
    {
        if (!SourceCache::isSupported()) {
            self::markTestSkipped('APCu is not enabled');
        }

        $kernel = $this->createKernel('empty_with_cache.yml');
        $container = $kernel->getContainer();

        /** @var ContainerAwareController $class */
        $class = $container->get(ContainerAwareController::class);

        self::assertSame($container, $class->container);
    }

    /**
     * @link https://github.com/PHP-DI/Symfony-Bridge/issues/2
     */
    public function testContainerAwareWithoutCacheAndWithCompilation()
    {
        $kernel = $this->createKernel('empty_with_compilation.yml');
        $container = $kernel->getContainer();

        /** @var ContainerAwareController $class */
        $class = $container->get(ContainerAwareController::class);

        self::assertSame($container, $class->container);
    }
}
