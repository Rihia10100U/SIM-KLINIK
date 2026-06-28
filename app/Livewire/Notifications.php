<?php

namespace App\Livewire;

use App\Models\Notification;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Notifications extends Component
{
    use WithPagination;

    public string $title = 'Notifikasi';

    public function render()
    {
        return view('livewire.notifications', [
            'notifications' => Notification::forUser(auth()->id())
                ->latest()
                ->paginate(20),
        ]);
    }

    public function markAsRead(int $id): void
    {
        $notif = Notification::where('user_id', auth()->id())->findOrFail($id);
        $notif->markAsRead();
    }

    public function markAllAsRead(): void
    {
        Notification::forUser(auth()->id())->unread()->update(['is_read' => true]);
    }
}
