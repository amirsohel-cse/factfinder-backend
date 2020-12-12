<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskRemainderNotification extends Notification
{
    use Queueable;
    public $task;
    public $left_days;
    public $message;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Task $task, $left_days,$message)
    {
        $this->task = $task;
        $this->left_days = $left_days;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['broadcast','database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'task_id' => $this->task->id,
            'days_left' => $this->left_days,
            'task_type' => $this->task->type,
            'description' => $this->task->description,
            'deadline' =>  date('d F Y', strtotime($this->task->completion_date)),
            'time' =>  date('H:i:A', strtotime($this->task->completion_date)),
            'assigned_by' => $this->task->creator->first_name.' '.$this->task->creator->last_name,
            'message' => $this->message,
        ];
    }
}
