<?php

namespace App\Jobs;

class TestJob
{
    public function run(string $message = 'Hello from the background job!')
    {
        echo "[TestJob] Message: $message" . PHP_EOL;

        sleep(2);
    }
}
