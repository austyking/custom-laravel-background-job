<?php

if (!function_exists ('runBackgroundJob')) {
    function runBackgroundJob($class, $method, array $params): false|string|null
    {
        $phpPath = config ('runner.php.path');
        $scriptPath = base_path ('app/Runners/scripts/BackgroundJobScript.php');
        $params = escapeshellarg (json_encode ($params));

        $command = "$phpPath $scriptPath \"$class\" \"$method\" $params > /dev/null 2>&1 &";
        return shell_exec ($command);
    }
}
