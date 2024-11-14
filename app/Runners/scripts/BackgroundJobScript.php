<?php

$class = $argv[1];
$method = $argv[2];
$params = json_decode ($argv[3], true);

try {
    \App\Runners\Runner::run ($class, $method, $params);
} catch (\Exception $e) {
    file_put_contents (storage_path ('logs/background_jobs_errors.log'), $e->getMessage () . PHP_EOL, FILE_APPEND);
}
