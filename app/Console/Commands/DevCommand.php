<?php

namespace App\Console\Commands;

use App\Http\Filters\Duplicate;
use App\Models\Firmware;
use Illuminate\Console\Command;
use Illuminate\Pipeline\Pipeline;

// use Illuminate\Routing\Pipeline;

class DevCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        request()->merge(['is_duplicate' => true]);

        $firmwares = app()->make(Pipeline::class)
            ->send(Firmware::query())
            ->through([
                Duplicate::class
            ])
            ->thenReturn();

        dd($firmwares->get());
    }
}







// Firmware::where('is_duplicate', true)->update(['is_duplicate' => false]);