<?php

namespace App\Livewire;

use App\Models\Notification;
use Livewire\Component;

class NotificationBell extends Component
{
    public int $unreadCount = 0;

    public function mount(): void
    {
        $this->unreadCount = $this->getUnreadCount();
    }

    public function render()
    {
        return view('livewire.notification-bell', [
            'notifications' => Notification::forUser(auth()->id())
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }

    public function markAsRead(int $id): void
    {
        $notif = Notification::where('user_id', auth()->id())->findOrFail($id);
        $notif->markAsRead();
        $this->unreadCount = $this->getUnreadCount();
    }

    public function markAllAsRead(): void
    {
        Notification::forUser(auth()->id())->unread()->update(['is_read' => true]);
        $this->unreadCount = 0;
    }

    public function refresh(): void
    {
        $this->unreadCount = $this->getUnreadCount();
    }

    private function getUnreadCount(): int
    {
        return Notification::forUser(auth()->id())->unread()->count();
    }
}
