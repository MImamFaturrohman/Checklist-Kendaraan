<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class BbmReportTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function title(): string
    {
        return 'Template Import BBM';
    }

    /**
     * Urutan kolom:
     * Tanggal, Waktu, Shift, Nomor Polisi, Jenis Kendaraan, Username,
     * Jenis Pengisian, KM Sebelum, KM Sesudah, Total KM, Volume, Harga,
     * Total Harga, Foto Odometer, Foto Struk
     */
    public function headings(): array
    {
        return [
            'tanggal',
            'waktu',
            'shift',
            'nomor_polisi',
            'jenis_kendaraan',
            'username',
            'jenis_pengisian',
            'km_sebelum',
            'km_sesudah',
            'total_km',
            'volume',
            'harga',
            'total_harga',
            'foto_odometer',
            'foto_struk',
        ];
    }

    public function array(): array
    {
        // Satu baris contoh (komentar / panduan)
        return [
            [
                '14-07-2026',     // tanggal (format d-m-Y)
                '08:30',          // waktu (format H:i)
                'pagi',           // shift: pagi / siang
                'B 1234 ABC',     // nomor_polisi (harus sesuai master armada)
                'Mobil',          // jenis_kendaraan (opsional, otomatis dari data kendaraan)
                'john.doe',       // username (harus terdaftar di sistem)
                'Operasional',    // jenis_pengisian: Operasional / SPPD / (kosong = Operasional)
                45000,            // km_sebelum
                45120,            // km_sesudah
                120,              // total_km (opsional, diabaikan saat import)
                40,               // volume (liter)
                13500,            // harga (per liter)
                540000,           // total_harga
                'odometer_123.jpg', // foto_odometer (nama file saja)
                'struk_123.jpg',    // foto_struk (nama file saja)
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 16, // tanggal
            'B' => 10, // waktu
            'C' => 10, // shift
            'D' => 18, // nomor_polisi
            'E' => 20, // jenis_kendaraan
            'F' => 18, // username
            'G' => 24, // jenis_pengisian
            'H' => 14, // km_sebelum
            'I' => 14, // km_sesudah
            'J' => 12, // total_km
            'K' => 12, // volume
            'L' => 14, // harga
            'M' => 16, // total_harga
            'N' => 24, // foto_odometer
            'O' => 24, // foto_struk
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastCol = 'O';
        $lastRow = $sheet->getHighestRow();

        // Header row styling
        $headerStyle = [
            'font' => [
                'bold'  => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size'  => 11,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1D4ED8'], // Blue-700
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => false,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FF93C5FD'],
                ],
            ],
        ];

        // Example row styling (light blue tint)
        $exampleStyle = [
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFDBEAFE'], // blue-100
            ],
            'font' => [
                'italic' => true,
                'color'  => ['argb' => 'FF1E3A5F'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FF93C5FD'],
                ],
            ],
        ];

        // Set row height for header
        $sheet->getRowDimension(1)->setRowHeight(22);

        return [
            "A1:{$lastCol}1" => $headerStyle,
            "A2:{$lastCol}2" => $exampleStyle,
        ];
    }
}
