<?php
/**
 * Created by PhpStorm.
 * Author : Richard Déloge, richarddeloge@gmail.com, https://teknoo.software
 * Date: 13/06/2024
 * Time: 16:52
 */

namespace Teknoo\Tests\DI\SymfonyBridge\UnitTest\DependencyInjection\Support;

use Teknoo\DI\SymfonyBridge\Container\BridgeBuilderInterface;
use Teknoo\DI\SymfonyBridge\Extension\ExtensionInterface;

class ExtensionMock implements ExtensionInterface
{
    private static ?ExtensionMock $instance = null;
    
    public int $counter = 0;

    public static function create(): ExtensionInterface
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function configure(BridgeBuilderInterface $builder): ExtensionInterface
    {
        $this->counter++;

        return $this;
    }
}