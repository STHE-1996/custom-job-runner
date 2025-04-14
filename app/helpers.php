<?php

if (!function_exists('runBackgroundJob')) {
    function runBackgroundJob(string $class, string $method, array $params = [])
    {
        $classArg = str_replace('\\', '\\\\', $class); 
        $phpPath = PHP_BINARY;
        $script = base_path('run-job.php');

       
        $escapedParams = array_map('escapeshellarg', $params);
        $paramString = implode(' ', $escapedParams);

        $cmd = "{$phpPath} {$script} {$classArg} {$method} {$paramString}";

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            pclose(popen("start /B " . $cmd, "r")); 
        } else {
            exec($cmd . " > /dev/null 2>&1 &"); 
        }
    }
}

 
