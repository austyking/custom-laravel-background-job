# Custom Laravel Background Jobs (Runner)

This project is a Laravel-based application that manages and runs background jobs. It includes functionality to run, list, and cancel jobs.

## Requirements

- PHP 8.2
- Composer
- Node with npm
- Laravel

## Installation

1. Clone the repository:
    ```sh
    git clone https://github.com/austyking/custom-laravel-background-job.git
    cd custom-laravel-background-job
    ```

2. Install PHP dependencies:
    ```sh
    composer install
    ```

3. Install JavaScript dependencies:
    ```sh
    npm install
    ```

4. Copy the `.env.example` file to `.env` and configure your environment variables:
    ```sh
    cp .env.example .env
    ```

5. Generate an application key:
    ```sh
    php artisan key:generate
    ```

6. Run database migrations:
    ```sh
    php artisan migrate
    ```

7. Serve the application (to view the dashboard):
    ```sh
    php artisan serve
    ```

8. Start the vite server as well:
    ```sh
    npm run dev
    ```

## Access the Dashboard
Register an account by clicking "Register" in the top-right corner of the homepage or navigate to `{your-host}/register`. After registration, you can log in and access the dashboard.

For the purpose of this Demo, I have created a route to run jobs. You can navigate to the `{your-host}/run-job` route to run a job. A default Job has been created which will run when this route is called.

NOTE: You will have to refresh the dashboard to see the updated status of the job.

The job **simply logs 1 to 50** in the default laravel.log file with a **sleep(1)** interval, so you may have time to refresh the dashboard and see the status(s). You can see the job in the `app/Runners/Jobs` directory.

## Configuration

The configuration for the runner jobs can be found in the `config/runner.php` file. You can set the paths for PHP, log files, and job retry settings.

### Environment Variables

The `.env` file includes several environment variables related to the runner jobs:

- `RUNNER_SUCCESS`: Status message for successful jobs.
- `RUNNER_FAILURE`: Status message for failed jobs.
- `PHP_PATH`: Path to the PHP executable. May be absolute or aliased
- `RUNNER_RETRY_ATTEMPTS`: Number of retry attempts for failed jobs.
- `RUNNER_RETRY_DELAY`: Delay between retry attempts.
- `RUNNER_LOG_PATH`: **Absolute** path to the log file for runner jobs.
- `RUNNER_ERROR_LOG_PATH`: **Absolute** path to the error log file for background jobs.

These could also be set in the `config/runner.php` configuration file

##### Note: Log file paths MUST be absolute paths

## Usage

### Creating a Job
To create a new job, run the command `php artisan runner:job {className}`. For example:
```sh
php artisan runner:job AdditionJob
```
Job files are by default stored in the `app/Runners/Jobs` directory. The generated job file will look like this:
```php
public function handle(/* \$params */)
{
    // Your code here
}
```

### Registering a Job
Unfortunately, Jobs are not automatically registered, at the moment.
To register a job, add the Job class and method in the `jobs` array inside `config/runner.php`. For example:
```php
'jobs' => [
    \App\Runners\Jobs\AdditionJob::class => 'handle',
],
```

### Running a Job

Runner exposes a helper function `runBackgroundJob` which allows you to run Jobs anywhere in your application. For example:
```php
runBackgroundJob(AdditionJob::class, 'handle', [50]);
```

For the purpose of this Demo, I have created a route to run jobs. You can navigate to the `{your-host}/run-job` route to run a job. This will run the controller method `runJob` in the `RunnerJobsController`.

```php
class RunnerJobsController extends Controller
{
    public function runJob(): void
        {
            Log::info ('Running Job');
            runBackgroundJob (AdditionJob::class, 'handle', [50]);
        }
}
```

### Listing Jobs

To list all jobs, navigate to the `/dashboard` route. This will display a table of all jobs with their details. This could be found here: `RunnerJobsController@index`.
```php
class RunnerJobsController extends Controller
{
    public function index()
    {
        $jobs = RunnerJob::all();
        return view('dashboard', compact('jobs'));
    }
}
```


### Cancelling a Job

To cancel a job, use the `cancelJob` provided by `Runner` method in the `RunnerJobsController`. For example:

```php
$job = RunnerJob::findOrFail($id);
$cancelled = Runner::cancelJob($job->id);
```

You can see it in action here: `RunnerJobsController@cancel`

```php
class RunnerJobsController extends Controller
{
    public function cancel($id)
    {
        $job = RunnerJob::findOrFail($id);
        $cancelled = Runner::cancelJob($job->id);
    
        if (!$cancelled) {
            return redirect()->back()->with('error', 'Job could not be cancelled.');
        }
    
        $job->update(['status' => 'cancelled']);
        return redirect()->back()->with('success', 'Job cancelled successfully.');
    }
}
```

## Logging

By default, logs for the background jobs are stored in the `logs/background_jobs.log` and error logs are stored in `logs/background_jobs_errors.log`.

You can configure the log paths in the `config/runner.php` file.

## Areas of Improvement
1. **Job Registration Automation**: Automate the registration of jobs to avoid manual updates in the config/runner.php file.
2. **Job Scheduling**: Implement a job scheduling system to run jobs at specific times.
3. **Job Prioritization**: Implement a job prioritization system to run high-priority jobs first.

## Current Limitations to this project
1. **Job Registration**: Jobs are not automatically registered. You have to manually add them to the `config/runner.php` file.
2. **Job Scheduling**: Jobs are not scheduled to run at specific times.
3. **Job Prioritization**: You cannot prioritize Jobs based on their importance.

## License

This project is licensed under the MIT License.
