<?php


namespace Teknoo\Tests\DI\Bridge\Symfony\FunctionalTest;

use Teknoo\Tests\DI\Bridge\Symfony\FunctionalTest\Fixtures\Kernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractFunctionalTest extends TestCase
{
    protected function createKernel($configFile = 'empty.yml')
    {
        // Clear the cache
        $fs = new Filesystem();
        $fs->remove(__DIR__ . '/Fixtures/cache/dev');

        $kernel = new Kernel($configFile);
        $kernel->boot();

        return $kernel;
    }
}
