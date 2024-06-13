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

namespace Teknoo\Tests\DI\SymfonyBridge\UnitTest\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Teknoo\DI\SymfonyBridge\DependencyInjection\Configuration;

#[CoversClass(Configuration::class)]
class ConfigurationTest extends TestCase
{
    public function buildInstance(): Configuration
    {
        return new Configuration();
    }

    public function testGetConfigTreeBuilder()
    {
        $treeBuilder = $this->buildInstance()->getConfigTreeBuilder();

        self::assertInstanceOf(
            TreeBuilder::class,
            $treeBuilder
        );
    }
}
