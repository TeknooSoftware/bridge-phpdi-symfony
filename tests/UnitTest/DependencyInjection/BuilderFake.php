<?php

declare(strict_types=1);

/**
 * Created by PhpStorm.
 * Author : Richard Déloge, richarddeloge@gmail.com, https://teknoo.software
 * Date: 13/06/2024
 * Time: 16:52
 */
namespace Teknoo\Tests\DI\SymfonyBridge\UnitTest\DependencyInjection;

use DI\ContainerBuilder as DIContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder as SfContainerBuilder;
use Teknoo\DI\SymfonyBridge\Container\BridgeBuilderInterface;

class BuilderFake implements BridgeBuilderInterface
{
    public function __construct(DIContainerBuilder $diBuilder, SfContainerBuilder $sfBuilder)
    {
    }

    public function prepareCompilation(?string $compilationPath): BridgeBuilderInterface
    {
        return $this;
    }

    public function enableCache(bool $enable): BridgeBuilderInterface
    {
        return $this;
    }

    public function loadDefinition(array $definitions): BridgeBuilderInterface
    {
        return $this;
    }

    public function import(string $diKey, string $sfKey): BridgeBuilderInterface
    {
        return $this;
    }

    public function initializeSymfonyContainer(): BridgeBuilderInterface
    {
        return $this;
    }
}
