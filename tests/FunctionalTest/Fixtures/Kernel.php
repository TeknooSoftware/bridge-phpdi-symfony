<?php

/*
 * Symfony Bridge.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 *
 * @link        http://teknoo.software/bridge-phpdi-symfony Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

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

    public function registerBundles(): array
    {
        return [new DIBridgeBundle()];
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
