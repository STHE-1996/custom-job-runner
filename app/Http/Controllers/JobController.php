<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class JobController extends Controller
{
    /**
     *
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function retryJob(Request $request)
    {
        $jobId = $request->input('job_id');

        $logFile = storage_path('logs/background_jobs.log');
        $logs = file($logFile);

        foreach ($logs as $log) {
            if (strpos($log, $jobId) !== false) {
                preg_match('/\[(.*?)\]\s+(🔄|❌|✅)\s+\[(.*?)\]\s+\[(.*?)\]\s+Starting job with params: (.*)/', $log, $matches);

                if (count($matches) > 0) {
                    $className = $matches[3]; 
                    $params = json_decode($matches[5], true);  
                    $job = app()->make($className); 
                    dispatch($job->withChain([/* optional job chain */]));  
                    Log::info("Job [$jobId] has been retried.");

                    return redirect()->back()->with('success', 'Job retried successfully!');
                }
            }
        }

        return redirect()->back()->with('error', 'Job not found in logs.');
    }

    /**
     * 
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancelJob(Request $request)
{
    $jobId = $request->input('job_id');

    $logFile = storage_path('logs/background_jobs.log');
    $logs = file($logFile);
    $newLogs = [];

    $removed = false;

    foreach ($logs as $log) {
        if (strpos($log, $jobId) === false) {
            $newLogs[] = $log;
        } else {
            $removed = true;
            Log::info("Job [$jobId] has been canceled and removed from logs.");
        }
    }

    file_put_contents($logFile, implode("", $newLogs));

    return response()->json([
        'success' => $removed,
        'message' => $removed ? 'Job canceled and removed.' : 'Job not found.'
    ]);
}


    /**
     *
     *
     * @param string $jobId
     */
    private function cancelRunningJob($jobId)
    {
        Log::info("Simulating cancellation of job [$jobId].");
    }

    /**
     *
     *
     * @param string $jobId
     * @return bool|string
     */
    private function findJobById($jobId)
    {
        $logFile = storage_path('logs/background_jobs.log');
        $logs = file($logFile);

        foreach ($logs as $log) {
            if (strpos($log, $jobId) !== false) {
                return $log; 
            }
        }

        return false;
    }

    /**
     * 
     *
     * @param string $message
     * @param string $type
     */
    private function logJob(string $message, string $type = 'info')
    {
        $timestamp = date('Y-m-d H:i:s');
        $logLine = "[$timestamp] $message\n";
        file_put_contents(storage_path('logs/background_jobs.log'), $logLine, FILE_APPEND);
    }

    public function showDashboard()
{
    $logFile = storage_path('logs/background_jobs.log');
    $logs = file_exists($logFile) ? file($logFile) : [];

    $totalFailed = 0;
    $totalSucceeded = 0;
    $totalStarting = 0;

    foreach ($logs as $log) {
        if (str_contains($log, '✅')) {
            $totalSucceeded++;
        } elseif (str_contains($log, '❌')) {
            $totalFailed++;
        } elseif (str_contains($log, '🔄')) {
            $totalStarting++;
        }
    }

    return view('your-dashboard-view-name', [
        'logs' => $logs,
        'totalFailed' => $totalFailed,
        'totalSucceeded' => $totalSucceeded,
        'totalStarting' => $totalStarting,
    ]);
}

}