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

use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\Class2;
use Psr\Container\ContainerInterface;
use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\ContainerAwareController;

/**
 * @coversNothing
 */
class KernelTest extends AbstractFunctionalTest
{
    public function testKernelShouldBoot()
    {
        $kernel = $this->createKernel();

        self::assertInstanceOf(ContainerInterface::class, $kernel->getContainer());
    }

    public function testSymfonyShouldResolveClassesFromYaml()
    {
        $kernel = $this->createKernel('class2.yml');

        $object = $kernel->getContainer()->get('class2');
        self::assertInstanceOf(Class2::class, $object);
    }

    public function testSymfonyShouldResolveClassesFromDI()
    {
        $kernel = $this->createKernel('empty.yml');

        $object = $kernel->getContainer()->get(ContainerAwareController::class);
        self::assertInstanceOf(ContainerAwareController::class, $object);
    }
}
