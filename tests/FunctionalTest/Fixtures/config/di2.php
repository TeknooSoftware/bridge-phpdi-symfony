<?php

declare(strict_types=1);

use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\Class3;

use function DI\get;
use function DI\create;

return [
    Class3::class => create()->constructor(get('class2_import')),
];
