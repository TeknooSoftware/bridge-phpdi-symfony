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
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/di-symfony-bridge Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\DI\SymfonyBridge\Container;

use DI\Container as DIContainer;
use DI\ContainerBuilder as DIContainerBuilder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Container as SfContainer;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Bridge implements ContainerInterface
{
    use BridgeTrait;

    private DIContainerBuilder $diBuilder;

    private SfContainer $sfContainer;

    private array $definitionsFiles;

    private array $definitionsImport;

    private ?DIContainer $diContainer = null;

    public function __construct(
        DIContainerBuilder $diBuilder,
        SfContainer $sfContainer,
        array $definitionsFiles,
        array $definitionsImport
    ) {
        $this->diBuilder = $diBuilder;
        $this->sfContainer = $sfContainer;
        $this->definitionsFiles = $definitionsFiles;
        $this->definitionsImport = $definitionsImport;
    }

    private function getDIContainer(): DIContainer
    {
        if (null !== $this->diContainer) {
            return $this->diContainer;
        }

        return $this->diContainer = $this->buildContainer(
            $this->diBuilder,
            $this,
            $this->definitionsFiles,
            $this->definitionsImport
        );
    }

    public function __invoke($id)
    {
        return $this->getDIContainer()->get($id);
    }

    public function get($id)
    {
        if ($this->sfContainer->has($id)) {
            return $this->sfContainer->get($id);
        }

        $diContainer = $this->getDIContainer();
        if ($diContainer->has($id)) {
            return $this->diContainer->get($id);
        }

        throw new ServiceNotFoundException($id);
    }

    public function has($id)
    {
        if ($this->sfContainer->has($id)) {
            return true;
        }

        $diContainer = $this->getDIContainer();
        return $diContainer->has($id);
    }
}