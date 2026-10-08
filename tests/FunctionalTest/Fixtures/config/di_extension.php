<?php

declare(strict_types=1);

use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\Product;

use function DI\create;

return [
    'extension.product' => create(Product::class),
];
