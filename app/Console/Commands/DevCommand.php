<?php

namespace App\Console\Commands;

use App\Http\Filters\Duplicate;
use App\Mail\RegistrationConfirmation;
use App\Models\Firmware;
use Illuminate\Console\Command;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Mail;

// use Illuminate\Routing\Pipeline;

class DevCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:go';

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
        $user = 'heturion';

        Mail::to('heturion@gmail.com')->send(new RegistrationConfirmation($user));
    }
}
