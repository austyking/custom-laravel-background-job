<?php

namespace App\Runners;

use Illuminate\Support\Facades\Log;

class Runner
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * @throws \Exception
     */
    public static function run($class, $method, array $params = [])
    {
        $attempts = config ('runner.retry.attempts');
        $delay = config ('runner.retry.delay');
        $classInstance = new $class();

        self::runnerLog ('Running', $class, $method, $params);

        for ($i = 1; $i <= $attempts; $i++) {
            try {
                $results = self::execute ($classInstance, $class, $method, $params);
                self::runnerLog ('Completed', $class, $method, $params);
                return $results;
            } catch (\Exception $e) {
                sleep ($delay);
                if ($i === $attempts) {
                    self::runnerErrorLog ($class, $method, $params, config ('runner.result.error'), $e->getMessage ());
                    self::runnerLog ('Failed', $class, $method, $params);
                    throw $e;
                }
            }
        }
    }

    protected static function runnerLog($state, $class, $method, $params = []): void
    {
        $stringParams = implode (', ', $params);
        file_put_contents (storage_path ('logs/background_jobs.log'), "----- Background Job $state: $class->$method($stringParams) ----- : " . now ()->format ('Y-m-d H:i:s') . PHP_EOL, FILE_APPEND);
    }

    protected static function runnerErrorLog($class, $method, $params, $status, $result): void
    {
        $data = [
            'class' => $class,
            'method' => $method,
            'parameters' => $params,
            'status' => $status,
            'result' => $result,
            'timestamp' => now ()->format ('Y-m-d H:i:s')
        ];

        if ($status === config ('runner.result.success')) {
            Log::info ("Background Job $status", [$data]);
        } else {
            Log::error ("Background Job $status", [$data]);
        }
    }

    protected static function execute($classInstance, $class, $method, $params)
    {
        $result = $classInstance->$method(...$params);
        self::runnerErrorLog ($class, $method, $params, config ('runner.result.success'), $result);
        return $result;
    }
}
