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
            $printer->setEmphasis(true);
            $printer->text($namaKlinik."\n");
            $printer->setEmphasis(false);
            $printer->text($waktu->format('d-m-Y H:i')."\n");
            $printer->feed();

            $printer->setTextSize(2, 2);
            $printer->setEmphasis(true);
            $printer->text($kodeAntrian."\n");
            $printer->setEmphasis(false);
            $printer->setTextSize(1, 1);

            $printer->feed();
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
            $printer->setEmphasis(true);
            $printer->text($namaKlinik."\n");
            $printer->setEmphasis(false);
            $printer->text($waktu->format('d-m-Y H:i')."\n");
            $printer->feed();

            $printer->setTextSize(2, 2);
            $printer->setEmphasis(true);
            $printer->text($kodeAntrian."\n");
            $printer->setEmphasis(false);
            $printer->setTextSize(1, 1);

            $printer->feed();
            $printer->text($namaPoli."\n");
            $printer->text($namaPasien."\n");
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
     * Pembungkus umum: buka koneksi, inisialisasi printer, jalankan isi struk lewat callback,
     * lalu potong & tutup.
     */
    private function cetak(callable $isiStruk): bool
    {
        $connector = $this->buatKoneksi();

        if (! $connector) {
            return false;
        }

        try {
            $printer = new Printer($connector);

            $printer->initialize();
            $this->inisialisasiCharset($printer);

            $isiStruk($printer);

            $printer->feed();
            $printer->cut();
            $printer->close();

            return true;
        } catch (Throwable $e) {
            report($e);

            return false;
        }
    }

    /**
     * Setel code page untuk karakter Indonesia (Latin1/CP850).
     */
    private function inisialisasiCharset(Printer $printer): void
    {
        $printer->selectCharacterTable(2);
    }

    private function buatKoneksi(): mixed
    {
        return match (config('printer.connection')) {
            'network' => new NetworkPrintConnector(
                config('printer.host'),
                config('printer.port'),
            ),
            'windows' => new WindowsPrintConnector(
                config('printer.windows_name'),
            ),
            'com' => new FilePrintConnector(
                '\\\\.\\'.config('printer.com_port'),
            ),
            'usb' => new FilePrintConnector(
                config('printer.usb_path'),
            ),
            default => null,
        };
    }
}
