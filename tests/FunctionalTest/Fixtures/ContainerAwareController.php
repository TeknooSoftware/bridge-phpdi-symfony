<?php

namespace Teknoo\Tests\DI\Bridge\Symfony\FunctionalTest\Fixtures;

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
