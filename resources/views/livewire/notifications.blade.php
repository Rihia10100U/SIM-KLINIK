<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-bold text-gray-800">Notifikasi</h2>
            <p class="text-sm text-gray-400 mt-1">Semua notifikasi untuk Anda</p>
        </div>
        @php $unread = $notifications->total() - $notifications->where('is_read', true)->count(); @endphp
        @if ($notifications->where('is_read', false)->count() > 0)
            <button
                wire:click="markAllAsRead"
                class="text-sm text-klinik-blue-dark hover:text-blue-800 font-medium"
            >
                Tandai semua dibaca
            </button>
        @endif
    </div>

    <div class="card divide-y divide-gray-100">
        @forelse ($notifications as $notif)
            <div class="px-5 py-4 flex items-start gap-4 {{ !$notif->is_read ? 'bg-klinik-blue-dark/5' : '' }}">
                @php
                    $colors = [
                        'info' => 'text-blue-500 bg-blue-50',
                        'success' => 'text-green-500 bg-green-50',
                        'warning' => 'text-amber-500 bg-amber-50',
                        'danger' => 'text-red-500 bg-red-50',
                    ];
                    $icons = [
                        'info' => 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z',
                        'success' => 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z',
                        'warning' => 'M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z',
                        'danger' => 'M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z',
                    ];
                @endphp
                <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 {{ $colors[$notif->type] ?? 'text-gray-400 bg-gray-50' }}">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="{{ $icons[$notif->type] ?? $icons['info'] }}"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-sm font-medium text-gray-800 {{ !$notif->is_read ? 'font-semibold' : '' }}">
                                @if ($notif->link)
                                    <a href="{{ $notif->link }}" class="hover:text-klinik-blue-dark" wire:click="markAsRead({{ $notif->id }})">{{ $notif->title }}</a>
                                @else
                                    {{ $notif->title }}
                                @endif
                            </p>
                            @if ($notif->message)
                                <p class="text-sm text-gray-500 mt-0.5">{{ $notif->message }}</p>
                            @endif
                        </div>
                        @if (!$notif->is_read)
                            <button wire:click="markAsRead({{ $notif->id }})" class="flex-shrink-0 text-gray-300 hover:text-gray-500 transition-colors" title="Tandai dibaca">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                </div>
            </div>
        @empty
            <div class="px-5 py-12 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3a4 4 0 00-4 4v2.8c0 2.6-1.8 3.6-1.8 3.6h11.6s-1.8-1-1.8-3.6V7a4 4 0 00-4-4z"/><path stroke-linecap="round" stroke-linejoin="round" d="M10 19a2 2 0 004 0"/>
                </svg>
                <p class="text-sm text-gray-400">Belum ada notifikasi</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $notifications->links() }}
    </div>
</div>
