<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace Teknoo\Tests\DI\SymfonyBridge\FunctionalTest;

use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\Class2;
use Psr\Container\ContainerInterface;
use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\ContainerAwareController;

/**
 * @coversNothing
 */
class KernelTest extends AbstractFunctionalTest
{
    public function testKernelShouldBoot()
    {
        $kernel = $this->createKernel();

        self::assertInstanceOf(ContainerInterface::class, $kernel->getContainer());
    }

    public function testSymfonyShouldResolveClassesFromYaml()
    {
        $kernel = $this->createKernel('class2.yml');

        $object = $kernel->getContainer()->get('class2');
        self::assertInstanceOf(Class2::class, $object);
    }

    public function testSymfonyShouldResolveClassesFromDI()
    {
        $kernel = $this->createKernel('empty.yml');

        $object = $kernel->getContainer()->get(ContainerAwareController::class);
        self::assertInstanceOf(ContainerAwareController::class, $object);
    }
}
