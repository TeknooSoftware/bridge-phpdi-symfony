<?php

namespace Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel as SymfonyKernel;
use Teknoo\DI\SymfonyBridge\DIBridgeBundle;

class Kernel extends SymfonyKernel
{
    private $configFile;

    public function __construct($configFile)
    {
        $this->configFile = $configFile;

        parent::__construct('dev', true);
    }

    public function registerBundles()
    {
        yield new DIBridgeBundle();
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    protected function getContainerClass(): string
    {
        return $this->randomName();
    }

    private function randomName(): string {
        $characters = 'abcdefghijklmnopqrstuvwxyz';
        $str = '';
        for ($i = 0; $i < 10; $i++) {
            $str .= $characters[\rand(0, \strlen($characters) - 1)];
        }

        return $str;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/' . $this->configFile);
    }
}
