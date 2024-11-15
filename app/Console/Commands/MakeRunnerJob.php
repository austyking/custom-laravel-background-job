<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeRunnerJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'runner:job {className}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new Custom Runner Job';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $className = $this->argument('className');
        $this->createRunnerJob($className);
    }

    /**
     * Create a new Runner Job
     *
     * @param string $className
     */
    protected function createRunnerJob(string $className): void
    {
        $jobsPath = config('runner.jobs_path');
        $filePath = $jobsPath . DIRECTORY_SEPARATOR . "$className.php";

        if (!File::exists($jobsPath)) {
            File::makeDirectory($jobsPath, 0755, true);
        }

        if (file_exists($filePath)) {
            $this->error('Runner Job already exists!');
            return;
        }

        $this->createRunnerJobFile($filePath, $className);
        $this->info('Runner Job created successfully.');
    }

    /**
     * Create a new Runner Job file
     *
     * @param string $filePath
     * @param string $className
     * @return int
     */
    protected function createRunnerJobFile(string $filePath, string $className): int
    {
        $classContent = <<<PHP
<?php

namespace App\Runners\Jobs;

class $className
{
    public function handle(/* \$params */)
    {
        // Your code here
    }
}
PHP;
        if (File::exists($filePath)) {
            $this->error("The Runner Job Class $className already exists at [$filePath]");
            return Command::FAILURE;
        }

        File::put($filePath, $classContent);
        $this->info("Runner Job [$filePath] created successfully");
        return Command::SUCCESS;
    }
}
