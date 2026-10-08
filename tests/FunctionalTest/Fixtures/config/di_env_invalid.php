<?php

declare(strict_types=1);

use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\Product;

use function DI\create;
use function DI\env;

return [
    'env.invalid' => env('DI_BRIDGE_TESTS_UNSET_VARIABLE', create(Product::class)),
];
