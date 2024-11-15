<?php

namespace App\Runners\Jobs;

use Illuminate\Support\Facades\Log;

class AdditionJob
{
    /**
     * @throws \Exception
     */
    public function handle($len = 5): bool
    {
        for ($i = 0; $i < $len; $i++) {
            sleep (1);
            \Illuminate\Support\Facades\Log::info ('Adding ' . $i);
        }
        return true;
    }
}
