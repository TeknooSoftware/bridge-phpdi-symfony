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
use Psr\Container\ContainerInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
trait BridgeTrait
{
    private function buildContainer(
        DIContainerBuilder $diBuilder,
        ContainerInterface $wrapContainer,
        array $definitionsFiles,
        array $definitionsImport
    ): DIContainer {
        $diBuilder->wrapContainer($wrapContainer);

        foreach ($definitionsFiles as $definitionFile) {
            $diBuilder->addDefinitions($definitionFile);
        }

        $imports = [];
        foreach ($definitionsImport as $diKey => $sfKey) {
            $imports[$diKey] = static function (ContainerInterface $container) use ($sfKey) {
                return $container->get($sfKey);
            };
        }
        $diBuilder->addDefinitions($imports);

        return $diBuilder->build();
    }
}
