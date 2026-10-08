<?php

declare(strict_types=1);

use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\Product;
use Teknoo\Tests\DI\SymfonyBridge\FunctionalTest\Fixtures\ProductFactory;

use function DI\factory;

return [
    'product.closure' => factory(fn (): Product => new Product()),
    'product.class_method' => factory([ProductFactory::class, 'create']),
    'product.class_static' => factory([ProductFactory::class, 'createStatic']),
    'product.string_static' => factory(ProductFactory::class . '::createStatic'),
    'product.string_method' => factory(ProductFactory::class . '::create'),
    'product.invokable_class' => factory(ProductFactory::class),
    'product.invokable_object' => factory(new ProductFactory()),
];
