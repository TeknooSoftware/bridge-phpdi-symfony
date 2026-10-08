<?php

declare(strict_types=1);

use function DI\env;
use function DI\string;
use function DI\value;

return [
    'percent.value' => value('a%b%c'),
    'percent.escaped' => value('100%%'),
    'percent.array' => value(['k' => 'x%y%', 'n' => ['z%']]),
    'percent.string' => string('{percent.value}/d'),
    'percent.env' => env('DI_BRIDGE_TESTS_UNSET_VARIABLE', 'd%e%f'),
];
