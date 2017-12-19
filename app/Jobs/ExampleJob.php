<?php

namespace App\Jobs;

use App\Models\User;
use App\Support\Util;
use App\Models\IosApp;

class ExampleJob extends Job
{
    protected $user;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Util::log('lala','5555');
    }
}
