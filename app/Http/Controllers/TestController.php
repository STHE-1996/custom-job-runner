<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function fireJob(Request $request)
    {
        runBackgroundJob(\App\Jobs\TestJob::class, 'run', ['This is async!']);
        
        return response()->json(['message' => 'Background job dispatched!']);
    }
}
