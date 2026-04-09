<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckOverdueBorrows extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-overdue-borrows';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for overdue borrows and send reminders for items due tomorrow.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->startOfDay();
        $tomorrow = now()->addDay()->startOfDay();

        // 1. Nhắc nhở trả trước 1 ngày
        $reminders = \App\Models\BorrowRequest::with('user')
            ->where('status', 'borrowing')
            ->whereDate('expected_return_date', $tomorrow)
            ->get();

        foreach ($reminders as $req) {
            /** @var \App\Models\BorrowRequest $req */
            if ($req->user) {
                $req->user->notify(new \App\Notifications\OverdueReminderNotification($req, 'reminder'));
            }
        }

        // 2. Chuyển trạng thái sang quá hạn và thông báo
        $overdues = \App\Models\BorrowRequest::with('user')
            ->where('status', 'borrowing')
            ->whereDate('expected_return_date', '<', $today)
            ->get();

        foreach ($overdues as $req) {
            /** @var \App\Models\BorrowRequest $req */
            $req->update(['status' => 'overdue']);
            
            if ($req->user) {
                $req->user->notify(new \App\Notifications\OverdueReminderNotification($req, 'overdue'));
            }
        }

        $this->info(sprintf('Processed! Reminders: %d, Overdues: %d.', $reminders->count(), $overdues->count()));
    }
}
