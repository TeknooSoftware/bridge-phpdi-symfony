<?php

declare(strict_types=1);

use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\Product;

use function DI\value;

return [
    'value.object' => value(new Product()),
    'value.closure' => value(fn (): Product => new Product()),
    'value.scalar' => value(42),
];
