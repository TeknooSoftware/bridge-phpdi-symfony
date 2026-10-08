<?php

declare(strict_types=1);

use function DI\get;

return [
    'circularA' => get('circularB'),
    'circularB' => get('circularA'),
];
