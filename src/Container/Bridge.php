<?php

/*
 * Symfony Bridge.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.software/bridge-phpdi-symfony Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\DI\SymfonyBridge\Container;

use DI\Container as DIContainer;
use DI\ContainerBuilder as DIContainerBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Container as SfContainer;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * PSR `ContainerInterface` implementation as bridge between Symfony's container and PHP-DI container.
 * For PHP-DI, this container will be injected as Fallback container.
 * For Symfony Container, each PHP-DI entry will be registered into Symfony, with this container as factory.
 * The container configuration is done via the method `buildContainer` in the `BridgeTrait` used also by the
 * `BridgeBuilder` during the compilation of the Symfony Container.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class Bridge implements ContainerInterface
{
    use BridgeTrait;

    private ?DIContainer $diContainer = null;

    /**
     * @param iterable<string> $definitionsFiles
     * @param array<string, string> $definitionsImport
     */
    public function __construct(
        private readonly DIContainerBuilder $diBuilder,
        private readonly SfContainer $sfContainer,
        private readonly iterable $definitionsFiles,
        private readonly array $definitionsImport,
        private readonly ?string $compilationPath = null,
        private readonly bool $cacheEnabled = false,
    ) {
    }

    /*
     * Get the PHP DI container (build it if necessary) to use it when this object is called as factory by the Symfony
     * Container
     */
    private function getDIContainer(): DIContainer
    {
        if (null !== $this->diContainer) {
            return $this->diContainer;
        }

        return $this->diContainer = $this->buildContainer(
            $this->diBuilder,
            $this,
            $this->definitionsFiles,
            $this->definitionsImport,
            $this->compilationPath,
            $this->cacheEnabled
        );
    }

    /**
     * Service Factory used by Symfony' container to get service instance from PHP-DI
     *
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function __invoke(string $id): mixed
    {
        $diContainer = $this->getDIContainer();

        if ($diContainer->has($id)) {
            $object = $diContainer->get($id);

            if ($object instanceof ContainerAwareInterface) {
                $object->setContainer($this->sfContainer);
            }

            return $object;
        }

        throw new ServiceNotFoundException($id);
    }

    /**
     * Get Service bridge used from PHP-DI's Definition to manage parameters access : In SF, Parameters are accessible
     * only via getParameter, but in PHP-DI via the method "get" like other definition.
     *
     * @param string $id
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function get($id): mixed
    {
        if ($this->sfContainer->has($id)) {
            return $this->sfContainer->get($id);
        }

        $diContainer = $this->getDIContainer();
        if ($diContainer->has($id)) {
            return $diContainer->get($id);
        }

        if ($this->sfContainer->hasParameter($id)) {
            return $this->sfContainer->getParameter($id);
        }

        throw new ServiceNotFoundException($id);
    }

    /**
     * Service checking bridge used from PHP-DI's Definition to manage parameters access : In SF, Parameters are
     * accessible only via getParameter, but in PHP-DI via the method "get" like other definition.
     *
     * @param string $id
     */
    public function has($id): bool
    {
        if ($this->sfContainer->has($id)) {
            return true;
        }

        $diContainer = $this->getDIContainer();
        return $diContainer->has($id) || $this->sfContainer->hasParameter($id);
    }
}
