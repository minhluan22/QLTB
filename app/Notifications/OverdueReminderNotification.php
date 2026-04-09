<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Models\BorrowRequest;

class OverdueReminderNotification extends Notification
{
    use Queueable;

    public $borrowRequest;
    public $type; // 'reminder' hoặc 'overdue'

    /**
     * Create a new notification instance.
     */
    public function __construct(BorrowRequest $borrowRequest, $type = 'overdue')
    {
        $this->borrowRequest = $borrowRequest;
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        if ($this->type === 'reminder') {
            return [
                'title' => 'Sắp đến hạn trả máy',
                'message' => 'Phiếu mượn #' . $this->borrowRequest->id . ' của bạn sẽ đến hạn trả vào ngày mai.',
                'url' => route('borrow-requests.show', $this->borrowRequest->id),
            ];
        }

        return [
            'title' => 'Quá hạn trả thiết bị',
            'message' => 'Phiếu mượn #' . $this->borrowRequest->id . ' đã quá hạn! Vui lòng trả thiết bị ngay.',
            'url' => route('borrow-requests.show', $this->borrowRequest->id),
        ];
    }
}
