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
];
