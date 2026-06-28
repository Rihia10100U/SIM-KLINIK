<?php

return [
    /*
    |--------------------------------------------------------------------
    | Koneksi Printer Thermal
    |--------------------------------------------------------------------
    | Pilihan:
    |   'none'    — nonaktif, pakai cetak browser (window.print())
    |   'windows' — printer ter-install sebagai shared printer di Windows
    |   'com'     — port serial/COM (USB virtual COM), mis. SmartCOM RPP02N via USB
    |   'network' — printer thermal dengan LAN/Ethernet (port ESC/POS umumnya 9100)
    |   'usb'     — device file langsung (Linux: /dev/usb/lp0)
    */
    'connection' => env('PRINTER_CONNECTION', 'none'),

    // Dipakai kalau connection = 'windows' — nama printer share di Windows
    // SmartCOM RPP02N biasanya terdaftar sebagai "SmartCOM RPP02N" setelah install driver.
    // Cek di Control Panel → Devices and Printers → klik kanan printer → Printer properties → nama.
    'windows_name' => env('PRINTER_WINDOWS_NAME', 'POS58'),

    // Dipakai kalau connection = 'com' — port COM (USB serial) di Windows
    // Cek di Device Manager → Ports (COM & LPT) → cari "USB Serial Port (COMx)"
    'com_port' => env('PRINTER_COM_PORT', 'COM1'),
    'com_baud' => env('PRINTER_COM_BAUD', '9600'),

    // Dipakai kalau connection = 'network' — printer thermal dengan LAN/WiFi
    'host' => env('PRINTER_HOST', '192.168.1.50'),
    'port' => env('PRINTER_PORT', 9100),

    // Dipakai kalau connection = 'usb' — printer USB langsung sebagai device file (Linux)
    'usb_path' => env('PRINTER_USB_PATH', '/dev/usb/lp0'),

    // Lebar kertas thermal dalam mm (58mm default untuk RPP02N)
    'paper_width' => env('PRINTER_PAPER_WIDTH', 58),
];
