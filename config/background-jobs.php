<?php

return [
    'allowed' => [
        App\Jobs\TestJob::class => ['run'],
    ],
    'max_attempts' => 3,
    'retry_delay' => 5,
];


