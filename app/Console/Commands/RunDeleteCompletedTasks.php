<?php

namespace App\Console\Commands;

use App\Jobs\DeleteCompletedTasks;
use Illuminate\Console\Command;

class RunDeleteCompletedTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete-completed-tasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch a job to delete completed tasks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DeleteCompletedTasks::dispatch();
        $this->info('DeleteCompletedTasks job dispatched.');
    }
}
