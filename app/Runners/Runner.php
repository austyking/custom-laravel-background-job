<?php

namespace App\Runners;

use App\Models\RunnerJob;
use Illuminate\Support\Facades\Log;

require_once __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

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
    public static function run($job, $class, $method, array $params = [])
    {
        $attempts = config ('runner.retry.attempts');
        $delay = config ('runner.retry.delay');
        $classInstance = new $class();

        self::validate ($class, $method);

        self::runnerLog ('Running', $class, $method, $params);

        for ($i = 1; $i <= $attempts; $i++) {
            try {
                $results = self::execute ($classInstance, $class, $method, $params);
                self::runnerLog ('Completed', $class, $method, $params);
                $job->update(['status' => 'completed']);
                return $results;
            } catch (\Exception $e) {
                sleep ($delay);
                if ($i === $attempts) {
                    self::runnerErrorLog ($class, $method, $params, config ('runner.result.error'), $e->getMessage ());
                    self::runnerLog ('Failed', $class, $method, $params);
                    $job->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                        'retry_count' => $job->retry_count + 1,
                    ]);
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

    /**
     * @throws \Exception
     */
    protected static function validate($class, $method): void
    {
        $registeredJobs = config ('runner.jobs');

        if (!(isset($registeredJobs[$class]) && $method == $registeredJobs[$class])) {
            throw new \Exception("Unauthorized job class or method.");
        }
    }

    public static function cancelJob($jobId): bool
    {
        $job = RunnerJob::findOrFail($jobId);

        if ($job->status === 'running' && $job->pid) {
            $pid = $job->pid;

            if (PHP_OS_FAMILY === 'Windows') {
                exec("taskkill /F /PID $pid");
            } else {
                exec("kill -9 $pid");
            }

            $job->update(['status' => 'cancelled']);
            self::runnerLog('Cancelled', $job->class, $job->method, $job->parameters);
            return true;
        }

        return false;
    }
}
