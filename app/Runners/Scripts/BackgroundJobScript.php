<?php

use App\Models\RunnerJob;
use App\Runners\Runner;

require_once __DIR__ . '/../../../vendor/autoload.php';
$app = require __DIR__ . '/../../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$class = $argv[1];
$method = $argv[2];
$params = json_decode ($argv[3], true);
$pid = getmypid ();

$job = RunnerJob::create ([
    'class' => $class,
    'method' => $method,
    'parameters' => $params,
    'status' => 'running',
    'pid' => $pid
]);

try {
    Runner::run ($job, $class, $method, $params);
} catch (Exception $e) {
    $job->update ([
        'status' => 'failed',
        'error_message' => $e->getMessage (),
        'retry_count' => $job->retry_count + 1
    ]);

    file_put_contents (storage_path ('logs/background_jobs_errors.log'), $e->getMessage () . PHP_EOL, FILE_APPEND);
}
