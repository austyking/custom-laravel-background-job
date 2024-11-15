<?php

namespace App\Runners\Jobs;

use Illuminate\Support\Facades\Log;

class AdditionJob
{
    public function handle($len = 5): bool
    {
        Log::info ('Running Addition Job');
        for ($i = 0; $i < $len; $i++) {
            sleep (1);
            \Illuminate\Support\Facades\Log::info ('Adding ' . $i);
        }
        return true;
    }
}
