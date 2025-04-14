<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class DashboardController extends Controller
{
    public function index()
    {
        $logPath = storage_path('logs/background_jobs.log');
        $logs = File::exists($logPath) ? File::get($logPath) : 'No logs found.';

        $lines = array_reverse(explode("\n", trim($logs)));

        // Initialize counters
        $totalFailed = 0;
        $totalSucceeded = 0;
        $totalStarting = 0;

        // Loop through log lines and count statuses
        foreach ($lines as $line) {
            if (str_contains($line, '❌')) {
                $totalFailed++;
            } elseif (str_contains($line, '✅')) {
                $totalSucceeded++;
            } elseif (str_contains($line, '🔄')) {
                $totalStarting++;
            }
        }    

        return view('dashboard', [
            'logs' => $lines,
            'totalFailed' => $totalFailed,
            'totalSucceeded' => $totalSucceeded,
            'totalStarting' => $totalStarting,
        ]);
    }
}
