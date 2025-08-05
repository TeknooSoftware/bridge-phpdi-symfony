<?php

/*
 * Symfony Bridge.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 *
 * @link        https://teknoo.software/libraries/php-di-symfony-bridge Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures;

use Override;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel as SymfonyKernel;
use Teknoo\DI\SymfonyBridge\DIBridgeBundle;

use function random_int;
use function strlen;

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

    #[Override]
    public function getProjectDir(): string
    {
        return __DIR__;
    }

    #[Override]
    protected function getContainerClass(): string
    {
        return $this->randomName();
    }

    private function randomName(): string {
        $characters = 'abcdefghijklmnopqrstuvwxyz';
        $str = '';
        for ($i = 0; $i < 10; ++$i) {
            $str .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $str;
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/config/' . $this->configFile);
    }
}
