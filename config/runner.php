<?php
return [
    'php' => [
        'path' => env ('PHP_PATH', 'php'),
    ],

    'retry' => [
        'attempts' => env ('RUNNER_RETRY_ATTEMPTS', 3),
        'delay' => env ('RUNNER_RETRY_DELAY', 5),
    ],

    'result' => [
        'success' => env ('RUNNER_SUCCESS', 'SUCCESSFUL'),
        'failure' => env ('RUNNER_FAILURE', 'FAILED'),
    ],

    'log' => [
        'path' => env ('RUNNER_LOG_PATH', storage_path ('logs/background_jobs.log')),
        'error' => env ('RUNNER_ERROR_LOG_PATH', storage_path ('logs/background_jobs_errors.log')),
    ],

    'jobs_path' => base_path ('app/Runners/Jobs'),



    /**
     * Add your jobs here
     * /path/to/job => 'method'
     */
    'jobs' => [
        \App\Runners\Jobs\AdditionJob::class => 'handle',
    ],
];
