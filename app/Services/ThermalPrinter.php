<?php

namespace App\Services;

use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;
use Throwable;

class ThermalPrinter
{
    /**
     * Cetak tiket nomor antrian PENDAFTARAN (dipakai di Kiosk Ruang Antrian).
     */
    public function cetakTiketAntrian(string $kodeAntrian, string $namaKlinik, \DateTimeInterface $waktu): bool
    {
        return $this->cetak(function (Printer $printer) use ($kodeAntrian, $namaKlinik, $waktu) {
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text($namaKlinik."\n");
            $printer->text("Nomor Antrian Pendaftaran\n");
            $printer->feed();

            $printer->setTextSize(4, 4);
            $printer->setEmphasis(true);
            $printer->text($kodeAntrian."\n");
            $printer->setEmphasis(false);
            $printer->setTextSize(1, 1);

            $printer->feed();
            $printer->text($waktu->format('d-m-Y H:i')."\n");
            $printer->text("Silakan tunggu nomor Anda dipanggil\n");
        });
    }

    /**
     * Cetak tiket nomor antrian POLI (dipakai di menu Cetak Antrian & setelah Daftarkan di Panggilan Pendaftaran).
     */
    public function cetakTiketPoli(
        string $kodeAntrian,
        string $namaPoli,
        string $namaPasien,
        string $namaKlinik,
        \DateTimeInterface $waktu
    ): bool {
        return $this->cetak(function (Printer $printer) use ($kodeAntrian, $namaPoli, $namaPasien, $namaKlinik, $waktu) {
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text($namaKlinik."\n");
            $printer->text("Nomor Antrian Poli\n");
            $printer->feed();

            $printer->setTextSize(4, 4);
            $printer->setEmphasis(true);
            $printer->text($kodeAntrian."\n");
            $printer->setEmphasis(false);
            $printer->setTextSize(1, 1);

            $printer->feed();
            $printer->text($namaPoli."\n");
            $printer->text($namaPasien."\n");
            $printer->text($waktu->format('d-m-Y H:i')."\n");
            $printer->text("Silakan tunggu di area poli\n");
        });
    }

    /**
     * Cetak struk uji coba — dipakai tombol "Tes Cetak" di halaman Pengaturan.
     */
    public function tesCetak(string $namaKlinik): bool
    {
        return $this->cetakTiketAntrian('TEST-001', $namaKlinik, now());
    }

    /**
     * Pembungkus umum: buka koneksi, jalankan isi struk lewat callback, lalu potong & tutup.
     * Tidak melempar exception ke pemanggil — return false kalau printer gagal dihubungi,
     * supaya alur kiosk/halaman tetap jalan walau printer mati/offline.
     */
    private function cetak(callable $isiStruk): bool
    {
        $connector = $this->buatKoneksi();

        if (! $connector) {
            return false;
        }

        try {
            $printer = new Printer($connector);

            $isiStruk($printer);

            $printer->feed(2);
            $printer->cut();
            $printer->close();

            return true;
        } catch (Throwable $e) {
            report($e);

            return false;
        }
    }

    private function buatKoneksi(): mixed
    {
        return match (config('printer.connection')) {
            'network' => new NetworkPrintConnector(config('printer.host'), config('printer.port')),
            'windows' => new WindowsPrintConnector(config('printer.windows_name')),
            'usb' => new FilePrintConnector(config('printer.usb_path')),
            default => null,
        };
    }
}
