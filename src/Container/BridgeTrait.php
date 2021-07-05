<?php

/*
 * Symfony Bridge.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
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

use function DI\get;

/**
 * Trait to create a new PHP-DI Container thanks ti PHP-DI Builder, wrapping a PSR Container instance as fallback
 * container, and add all DI definitions passed in `$definitionsFiles`.
 * FOr all entries in `$definitionsImport`, a new definition is added via the heleper `DI\get`.
 * The Cache and the Compilation can be enable via `$enableCache` and pass the path to write the compiled container in
 * `$compilationPath`.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
trait BridgeTrait
{
    /**
     * @param array<int, string> $definitionsFiles
     * @param array<string, string> $definitionsImport
     */
    private function buildContainer(
        DIContainerBuilder $diBuilder,
        ContainerInterface $wrapContainer,
        array $definitionsFiles,
        array $definitionsImport,
        ?string $compilationPath = null,
        bool $enableCache = false
    ): DIContainer {
        $diBuilder->wrapContainer($wrapContainer);

        foreach ($definitionsFiles as $definitionFile) {
            $diBuilder->addDefinitions($definitionFile);
        }

        $imports = [];
        foreach ($definitionsImport as $diKey => $sfKey) {
            $imports[$diKey] = get($sfKey);
        }
        $diBuilder->addDefinitions($imports);

        if (null !== $compilationPath) {
            $diBuilder->enableCompilation(
                $compilationPath,
                'CompiledContainer',
                CompiledContainer::class
            );
        }

        if (true === $enableCache) {
            $diBuilder->enableDefinitionCache();
        }

        return $diBuilder->build();
    }
}
