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
 * @link        https://teknoo.software/libraries/php-di-symfony-bridge Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
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
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait BridgeTrait
{
    /**
     * @param DIContainerBuilder<DIContainer> $diBuilder
     * @param iterable<string> $definitionsFiles
     * @param array<string, string> $definitionsImport
     */
    private function buildContainer(
        DIContainerBuilder $diBuilder,
        ContainerInterface $wrapContainer,
        iterable $definitionsFiles,
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
