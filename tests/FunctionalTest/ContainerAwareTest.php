<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace Teknoo\Tests\DI\Bridge\Symfony\FunctionalTest;

use Teknoo\Tests\DI\Bridge\Symfony\FunctionalTest\Fixtures\ContainerAwareController;

/**
 * @coversNothing
 */
class ContainerAwareTest extends AbstractFunctionalTest
{
    /**
     * @link https://github.com/PHP-DI/Symfony-Bridge/issues/2
     */
    public function testContainerAware()
    {
        $kernel = $this->createKernel();
        $container = $kernel->getContainer();

        /** @var ContainerAwareController $class */
        $class = $container->get(ContainerAwareController::class);

        self::assertSame($container, $class->container);
    }
}
