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

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerAwareController implements ContainerAwareInterface
{
    public ?ContainerInterface $container = null;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
