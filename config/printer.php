<?php

return [
    /*
    |--------------------------------------------------------------------
    | Koneksi Printer Thermal
    |--------------------------------------------------------------------
    | Pilihan: 'none' (nonaktif, pakai cetak browser saja), 'network', 'windows', 'usb'
    */
    'connection' => env('PRINTER_CONNECTION', 'none'),

    // Dipakai kalau connection = 'network' — printer thermal dengan LAN/WiFi (port ESC/POS umumnya 9100)
    'host' => env('PRINTER_HOST', '192.168.1.50'),
    'port' => env('PRINTER_PORT', 9100),

    // Dipakai kalau connection = 'windows' — printer ter-install sebagai printer share di Windows
    'windows_name' => env('PRINTER_WINDOWS_NAME', 'POS-58'),

    // Dipakai kalau connection = 'usb' — printer USB langsung sebagai device file (umumnya di Linux)
    'usb_path' => env('PRINTER_USB_PATH', '/dev/usb/lp0'),
];
