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

        try {
            self::runnerLog ('Running', $class, $method, $params);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage ());
        }

        for ($i = 1; $i <= $attempts; $i++) {
            try {
                $results = self::execute ($classInstance, $class, $method, $params);
                self::runnerLog ('Completed', $class, $method, $params);
                $job->update(['status' => 'completed']);
                return $results;
            } catch (\Exception $e) {
                try {
                    self::runnerErrorLog ($class, $method, $params, config ('runner.result.failure'), $e->getMessage ());
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage ());
                }
                sleep ($delay);

                $job->update([
                    'error_message' => $e->getMessage(),
                    'retry_count' => $job->retry_count + 1,
                ]);

                if ($i >= $attempts) {
                    try {
                        self::runnerLog ('Failed', $class, $method, $params);
                    } catch (\Exception $e) {
                        throw new \Exception($e->getMessage ());
                    }
                    $job->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                    ]);
                    self::killJobProcess ($job);
                    throw new \Exception($e->getMessage ());
                }
            }
        }
    }

    protected static function runnerLog($state, $class, $method, $params = []): void
    {
        $path = config ('runner.log.path');
        if (empty($path)){
            throw new \Exception('Log file Path empty. Ensure you have set absolute path in <strong><code class="text-blue-500">config/runner.php -> log -> path</code></strong>. Also make sure to <strong>remove (or comment out) related <code class="text-blue-500">env</code> variables</strong> if you want to use the value in config');
        }
        $stringParams = implode (', ', $params);
        file_put_contents (config ('runner.log.path'), "----- Background Job $state: $class->$method($stringParams) ----- : " . now ()->format ('Y-m-d H:i:s') . PHP_EOL, FILE_APPEND);
    }

    /**
     * @throws \Exception
     */
    protected static function runnerErrorLog($class, $method, $params, $status, $result): void
    {
        $path = config ('runner.log.error');
        if (empty($path)){
            throw new \Exception('Error Log file Path empty. Ensure you have set absolute path in <strong><code class="text-blue-500">config/runner.php -> log -> error</code></strong>. Also make sure to <strong>remove (or comment out) related <code class="text-blue-500">env</code> variables</strong> if you want to use the value in config');
        }

        $data = json_encode ([
            'class' => $class,
            'method' => $method,
            'parameters' => $params,
            'status' => $status,
            'result' => $result,
            'timestamp' => now ()->format ('Y-m-d H:i:s')
        ]);

        file_put_contents (config ('runner.log.error'), "----- Background Job $status: $data ----- : " . now ()->format ('Y-m-d H:i:s') . PHP_EOL, FILE_APPEND);
    }

    /**
     * @throws \Exception
     */
    protected static function execute($classInstance, $class, $method, $params)
    {
        try {
            $result = $classInstance->$method(...$params);
            if (!$result){
                throw new \Exception("$class->$method must have returned <code style='color: #FF2D20'>false</code>");
            }
            self::runnerErrorLog ($class, $method, $params, config ('runner.result.success'), $result);
            return $result;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage ());
        }
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
            self::killJobProcess ($job);

            $job->update(['status' => 'cancelled']);
            self::runnerLog('Cancelled', $job->class, $job->method, $job->parameters);
            return true;
        }

        return false;
    }

    private static function killJobProcess(RunnerJob $job): void
    {
        $pid = $job->pid;

        if (PHP_OS_FAMILY === 'Windows') {
            exec("taskkill /F /PID $pid");
        } else {
            exec("kill -9 $pid");
        }
    }
}
