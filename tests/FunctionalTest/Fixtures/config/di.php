<?php

use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\Class1;
use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\Class3;
use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\ContainerAwareController;

use function DI\get;
use function DI\create;

return [
    ContainerAwareController::class => create(),
    Class1::class => create()->constructor(get('class2')),
    Class3::class => create()->constructor(get('class2_import')),
];
