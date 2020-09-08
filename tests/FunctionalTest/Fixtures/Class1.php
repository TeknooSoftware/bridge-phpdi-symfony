<?php

namespace Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures;

class Class1
{
    public ?Class2 $param1 = null;

    public function __construct(Class2 $param1)
    {
        $this->param1 = $param1;
    }
}
