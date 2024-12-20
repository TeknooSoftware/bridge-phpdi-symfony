<?php

/*
 * DI Symfony Bridge.
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

namespace Teknoo\DI\SymfonyBridge\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Add new available keys into Symfony configuration, to configure this bundle, ideally to be defined in
 * /config/package/di_bridge.yaml :
 *     di_bridge:
 *          #Optional, To enable PHP-DI's container compilation (disable by default)
 *          compilation_path: ~ #Default, or path to store cache, like '%kernel.project_dir%/var/cache/phpdi'
 *          #Optional, To enable PHP-DI's cache (disable by default)
 *          enable_cache: false #Default or true
 *          definitions:
 *              - 'list of PHP-DI definitions file, you can use Symfony joker like %kernel.project_dir%'
 *              #example
 *              - '%kernel.project_dir%/vendor/editor_name/package_name/src/di.php'
 *              - file: '%kernel.project_dir%/config/di.php'
 *                priority: 10
 *          extensions:
 *              - 'service name or class name'
 *               - name: 'service name or class name'
 *                 priority: 10
 *          import: #Optional
 *              #To make alias from SF entries into PHPDI
 *              My\Class\Name: 'symfony.contaner.entry.name'
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('di_bridge');

        $root = $treeBuilder->getRootNode();
        $root->children()
            ->scalarNode('compilation_path')
                ->defaultNull()
            ->end()//compilation_path
            ->booleanNode('enable_cache')
                ->defaultFalse()
            ->end() //enable_cache
            ->arrayNode('definitions')
                ->arrayPrototype()
                    ->beforeNormalization()
                    ->ifString()
                        ->then(static fn($v): array => ['priority' => 0, 'file' => $v])
                    ->end()
                    ->children()
                        ->integerNode('priority')->end()
                        ->scalarNode('file')->end()
                    ->end()
                ->end()
            ->end() // definitions
            ->arrayNode('import')
                ->useAttributeAsKey('name')
                ->scalarPrototype()->end()
            ->end() //import
            ->arrayNode('extensions')
                ->arrayPrototype()
                    ->beforeNormalization()
                    ->ifString()
                        ->then(static fn($v): array => ['priority' => 0, 'name' => $v])
                    ->end()
                    ->children()
                        ->integerNode('priority')->end()
                        ->scalarNode('name')->end()
                    ->end()
                ->end()
            ->end() //extensions
        ->end();

        return $treeBuilder;
    }
}
