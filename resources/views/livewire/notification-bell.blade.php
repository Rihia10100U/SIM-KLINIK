<div
    x-data="{ open: false }"
    class="relative"
    @click.outside="open = false"
    wire:poll.10s="refresh"
>
    <button
        @click="open = !open"
        class="relative w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-200 transition-colors"
    >
        <x-icon name="bell" class="w-4 h-4" />
        @if ($unreadCount > 0)
            <span class="absolute -top-1 -right-1 inline-flex items-center justify-center w-4 h-4 text-[9px] font-bold text-white bg-red-500 rounded-full border-2 border-white">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden z-50"
    >
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-800">Notifikasi</h3>
            @if ($unreadCount > 0)
                <button
                    wire:click="markAllAsRead"
                    class="text-xs text-klinik-blue-dark hover:text-blue-800 font-medium"
                >
                    Tandai semua dibaca
                </button>
            @endif
        </div>

        <div class="max-h-80 overflow-y-auto">
            @forelse ($notifications as $notif)
                <div class="px-4 py-3 border-b border-gray-50 hover:bg-gray-50 transition-colors {{ !$notif->is_read ? 'bg-klinik-blue-dark/5' : '' }}">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5">
                            @php
                                $colors = [
                                    'info' => 'text-blue-500',
                                    'success' => 'text-green-500',
                                    'warning' => 'text-amber-500',
                                    'danger' => 'text-red-500',
                                ];
                                $icons = [
                                    'info' => 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z',
                                    'success' => 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z',
                                    'warning' => 'M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z',
                                    'danger' => 'M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z',
                                ];
                            @endphp
                            <svg class="w-4 h-4 {{ $colors[$notif->type] ?? 'text-gray-400' }}" fill="currentColor" viewBox="0 0 24 24">
                                <path d="{{ $icons[$notif->type] ?? $icons['info'] }}"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 {{ !$notif->is_read ? 'font-semibold' : '' }}">
                                @if ($notif->link)
                                    <a href="{{ $notif->link }}" class="hover:text-klinik-blue-dark" wire:click="markAsRead({{ $notif->id }})">{{ $notif->title }}</a>
                                @else
                                    {{ $notif->title }}
                                @endif
                            </p>
                            @if ($notif->message)
                                <p class="text-xs text-gray-500 mt-0.5">{{ $notif->message }}</p>
                            @endif
                            <p class="text-[10px] text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                        </div>
                        @if (!$notif->is_read)
                            <button wire:click="markAsRead({{ $notif->id }})" class="text-gray-300 hover:text-gray-500 transition-colors" title="Tandai dibaca">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-center">
                    <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3a4 4 0 00-4 4v2.8c0 2.6-1.8 3.6-1.8 3.6h11.6s-1.8-1-1.8-3.6V7a4 4 0 00-4-4z"/><path stroke-linecap="round" stroke-linejoin="round" d="M10 19a2 2 0 004 0"/>
                    </svg>
                    <p class="text-sm text-gray-400">Belum ada notifikasi</p>
                </div>
            @endforelse
        </div>

        @if ($notifications->count() >= 10)
            <div class="px-4 py-2 border-t border-gray-100 text-center">
                <a href="{{ route('notifications') }}" class="text-xs text-klinik-blue-dark hover:text-blue-800 font-medium">Lihat semua notifikasi</a>
            </div>
        @endif
    </div>
</div>
