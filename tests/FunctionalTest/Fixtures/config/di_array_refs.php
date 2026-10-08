<?php

declare(strict_types=1);

use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\Product;

use function DI\create;
use function DI\get;

return [
    'array.handlers' => [get('class2'), create(Product::class)],
    'array.nested' => ['level1' => ['level2' => get('class2')]],
    'array.plain' => ['a' => 1, 'b' => ['c' => 'd']],
];
