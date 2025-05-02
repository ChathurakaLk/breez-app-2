<?php

namespace App\Console\Commands;

use App\Mail\OverdueTaskNotification;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendOverdueTaskEmails extends Command
{
    protected $signature = 'tasks:send-overdue-emails';
    protected $description = 'Send emails for overdue tasks';

    public function handle()
    {
        $now = Carbon::now();

        $tasks = Task::where('status', '!=', 'Completed')
            ->where('time', '<', $now)
            ->get();

        foreach ($tasks as $task) {
            if ($task->user && $task->user->email) {
                Mail::to($task->user->email)->send(new OverdueTaskNotification($task));
            }
        }

        $this->info('Overdue task emails sent.');
    }
}
