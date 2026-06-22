@props(['name', 'class' => 'w-5 h-5'])

@php
    // Kumpulan path SVG sederhana (line-icon style), supaya tidak perlu
    // install library icon/JS tambahan. Tinggal tambah entry baru jika perlu ikon lain.
    $icons = [
        'cross' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>',
        'home' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5L12 3l9 7.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M5 9.5V20a1 1 0 001 1h3v-6h6v6h3a1 1 0 001-1V9.5"/>',
        'ticket' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 8a2 2 0 012-2h14a2 2 0 012 2v2a2 2 0 100 4v2a2 2 0 01-2 2H5a2 2 0 01-2-2v-2a2 2 0 100-4V8z"/>',
        'queue' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h10"/>',
        'user-plus' => '<circle cx="9" cy="8" r="3.2"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 21a6.5 6.5 0 0113 0"/><path stroke-linecap="round" stroke-linejoin="round" d="M19 8v6M22 11h-6"/>',
        'clipboard' => '<rect x="9" y="3" width="6" height="4" rx="1"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6M9 16h6"/>',
        'document-search' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14 3H6a2 2 0 00-2 2v14a2 2 0 002 2h12a2 2 0 002-2V8l-6-5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M14 3v5h6"/><circle cx="10" cy="15" r="2.2"/><path stroke-linecap="round" stroke-linejoin="round" d="M11.7 16.7L13.5 18.5"/>',
        'pill' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 3h6M10 3v6l-5.2 8.7A2.4 2.4 0 006.8 21h10.4a2.4 2.4 0 002-3.7L14 9V3"/><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14h9"/>',
        'credit-card' => '<rect x="2" y="6" width="20" height="14" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M2 10.5h20"/>',
        'tag' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 11.5V5a1 1 0 011-1h6.5a1 1 0 01.7.3l9 9a1 1 0 010 1.4l-6.5 6.5a1 1 0 01-1.4 0l-9-9a1 1 0 01-.3-.7z"/><circle cx="7.5" cy="7.5" r="1.2"/>',
        'chart' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 19V10M10 19V5M16 19v-8M3 19h17"/>',
        'cog' => '<circle cx="12" cy="12" r="3"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 2.5v3M12 18.5v3M5.1 5.1l2.1 2.1M16.8 16.8l2.1 2.1M2.5 12h3M18.5 12h3M5.1 18.9l2.1-2.1M16.8 7.2l2.1-2.1"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.3-4.3"/>',
        'bell' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3a4 4 0 00-4 4v2.8c0 2.6-1.8 3.6-1.8 3.6h11.6s-1.8-1-1.8-3.6V7a4 4 0 00-4-4z"/><path stroke-linecap="round" stroke-linejoin="round" d="M10 19a2 2 0 004 0"/>',
        'users' => '<circle cx="9" cy="8" r="3"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.2 20a6.8 6.8 0 0113.6 0"/><circle cx="17" cy="9" r="2.3"/><path stroke-linecap="round" stroke-linejoin="round" d="M15.8 13.3c2.7.5 4.7 2.8 4.9 5.7"/>',
        'wallet' => '<rect x="3" y="7" width="18" height="12" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 9.5h13.5A2.5 2.5 0 0119 12v0"/><circle cx="16" cy="13" r="1.3"/>',
        'chevron-up' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>',
        'logout' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 21H6a2 2 0 01-2-2V5a2 2 0 012-2h3"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 17l5-5-5-5"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12H9"/>',
        'download' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-4-4m4 4l4-4"/><path stroke-linecap="round" stroke-linejoin="round" d="M5 19h14"/>',
        'printer' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 9V4a1 1 0 011-1h10a1 1 0 011 1v5"/><rect x="4" y="9" width="16" height="7" rx="1.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M7 14h10v6a1 1 0 01-1 1H8a1 1 0 01-1-1v-6z"/>',
    ];
@endphp

<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="{{ $class }}">
    {!! $icons[$name] ?? '' !!}
</svg>
