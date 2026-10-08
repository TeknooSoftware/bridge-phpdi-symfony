<?php

declare(strict_types=1);

use function DI\env;
use function DI\get;
use function DI\value;

return [
    'env.default.source' => value('default from php-di'),
    'env.nested' => env('DI_BRIDGE_TESTS_UNSET_VARIABLE', get('env.default.source')),
    'env.symfony_parameter' => env('DI_BRIDGE_TESTS_UNSET_VARIABLE', get('kernel.environment')),
];
