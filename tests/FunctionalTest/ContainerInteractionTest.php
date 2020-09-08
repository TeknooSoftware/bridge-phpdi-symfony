<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace Teknoo\Tests\DI\SymfonyBridge\FunctionalTest;

use DI\Container;
use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\Class1;
use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\Class2;

/**
 * Tests interactions between containers, i.e. entries that reference other entries in
 * other containers.
 *
 * @coversNothing
 */
class ContainerInteractionTest extends AbstractFunctionalTest
{
    public function testPhpdiShouldGetEntriesFromSymfonyToConstructAndSymfonyGetInPHPDI()
    {
        //Class 2 is defined in Symfony
        //Class 1 is defined in PHP DI
        //Class 1 requires Class 2
        //So PHPDI requires an entry from Symfony
        //And Symfony Container must use PHPDI to get Class1
        $kernel = $this->createKernel('class2.yml');

        $class1 = $kernel->getContainer()->get(Class1::class);

        self::assertInstanceOf(Class1::class, $class1);
    }

    public function testPhpdiAliasesCanReferenceSymfonyEntries()
    {
        $kernel = $this->createKernel('class2.yml');
        $container = $kernel->getContainer();

        $class2 = $container->get('class2Alias');

        self::assertInstanceOf(Class2::class, $class2);
    }
}
