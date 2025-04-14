<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str; 

// Load Laravel app
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$allowedJobs = config('background-jobs.allowed', []);
$maxAttempts = config('background-jobs.max_attempts', 3);
$retryDelay = config('background-jobs.retry_delay', 5);

// Parse CLI args
[$script, $class, $method, $paramString] = $argv + [null, null, null, ''];
$class = str_replace('\\\\', '\\', $class); 
$params = $paramString !== '' ? explode(',', $paramString) : [];

// Extract delay from parameters if passed
$delay = 0;
foreach ($params as $key => $param) {
    if (str_starts_with($param, 'delay=')) {
        $delay = (int) str_replace('delay=', '', $param);
        unset($params[$key]); 
        $params = array_values($params); 
        break;
    }
}

$delayedUntil = now()->addSeconds($delay);

// Generate a unique Job ID for this run
$jobId = Str::uuid();  

// Validate job
if (!isset($allowedJobs[$class]) || !in_array($method, $allowedJobs[$class])) {
    echo "❌ Job not allowed: $class@$method\n";
    exit(1);
}

if ($delay > 0) {
    echo "⏳ Job is delayed for $delay seconds. It will run at " . $delayedUntil . "\n";
    sleep($delay);
}

$attempt = 1;
$retryCount = 0;  // Track the retry count
do {
    try {
        // Log job starting with unique Job ID and delay info
        logJob("🔄 [$jobId] [$class@$method] Starting job with params: " . json_encode($params) . " (Delayed for {$delay} seconds)", 'info');

        $instance = app()->make($class);
        $result = call_user_func_array([$instance, $method], $params);

        // Log job success with unique Job ID and delay info
        logJob("✅ [$jobId] [$class@$method] Success (Delayed for {$delay} seconds) - Retry Count: $retryCount", 'success');
        exit(0);
    } catch (\Throwable $e) {
        // Log job failure with unique Job ID, delay info, and error message
        $retryCount++;
        logJob("❌ [$jobId] [$class@$method] Attempt $attempt failed: " . $e->getMessage() . " (Delayed for {$delay} seconds) - Retry Count: $retryCount", 'error');
        file_put_contents(storage_path('logs/background_jobs_errors.log'), now() . " [$jobId] [$class@$method] " . $e->getMessage() . "\n", FILE_APPEND);
        if ($attempt >= $maxAttempts) {
            exit(1);
        }
        sleep($retryDelay);
        $attempt++;
    }
} while ($attempt <= $maxAttempts);

// Logger
function logJob(string $message, string $type = 'info') {
    $timestamp = date('Y-m-d H:i:s');
    $logLine = "[$timestamp] $message\n";
    file_put_contents(storage_path('logs/background_jobs.log'), $logLine, FILE_APPEND);
}
