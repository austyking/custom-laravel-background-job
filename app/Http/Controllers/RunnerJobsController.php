<?php

namespace App\Http\Controllers;

use App\Models\RunnerJob;
use App\Runners\Jobs\AdditionJob;
use App\Runners\Runner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RunnerJobsController extends Controller
{
    public function runJob(): void
    {
        Log::info ('Running Job');
        runBackgroundJob (AdditionJob::class, 'handle', [50]);
    }

    public function index()
    {
        $jobs = RunnerJob::latest()->get();
        return view('dashboard', compact('jobs'));
    }

    public function cancel($id)
    {
        $job = RunnerJob::findOrFail($id);

        $cancelled = Runner::cancelJob($job->id);

        if (!$cancelled) {
            return redirect ()->back()->with('error', 'Job could not be cancelled.');
        }

        $job->update(['status' => 'cancelled']);

        return redirect()->back()->with('success', 'Job cancelled successfully.');
    }
}
