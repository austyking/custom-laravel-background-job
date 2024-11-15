<?php

if (!function_exists ('runBackgroundJob')) {
    function runBackgroundJob(string $class, string $method, array $params = []): false|string|null
    {
        $phpPath = config ('runner.php.path');
        $scriptPath = base_path ('app/Runners/Scripts/BackgroundJobScript.php');
        $escapedParams = escapeshellarg (json_encode ($params));

        $command = sprintf(
            '%s %s %s %s %s',
            $phpPath,
            escapeshellarg($scriptPath),
            escapeshellarg($class),
            escapeshellarg($method),
            $escapedParams
        );

        if (PHP_OS_FAMILY === 'Windows') {
            pclose (popen ("start /B " . $command, "r"));
            return null;
        }

        return shell_exec ($command);
    }
}
