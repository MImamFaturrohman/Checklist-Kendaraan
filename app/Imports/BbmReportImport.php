<?php

namespace App\Imports;

use App\Models\BbmReport;
use App\Models\Kendaraan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class BbmReportImport implements ToCollection, WithHeadingRow, SkipsOnFailure
{
    use SkipsFailures;

    /** @var list<array{row: int, errors: list<string>}> */
    private array $importErrors = [];

    private int $successCount = 0;

    private int $skippedCount = 0;

    /** @var string|null Pesan error jika file tidak sesuai template */
    private ?string $fatalError = null;

    /**
     * Kolom wajib yang harus ada di heading Excel.
     * Key = nama kolom (setelah dinormalisasi oleh Maatwebsite),
     * Value = label ramah untuk ditampilkan ke user.
     */
    private const REQUIRED_COLUMNS = [
        'tanggal'         => 'tanggal',
        'waktu'           => 'waktu',
        'shift'           => 'shift',
        'nomor_polisi'    => 'nomor_polisi',
        'username'        => 'username',
        'km_sebelum'      => 'km_sebelum',
        'km_sesudah'      => 'km_sesudah',
        'volume'          => 'volume',
        'harga'           => 'harga',
    ];

    /**
     * Validasi apakah header kolom Excel sesuai template.
     * Maatwebsite sudah mengubah heading ke snake_case lowercase.
     *
     * @param  array<string, mixed>  $firstRow
     * @return string|null  Null = OK, string = pesan error
     */
    private function validateColumns(array $firstRow): ?string
    {
        $presentCols = array_keys($firstRow);
        $missing = [];

        foreach (self::REQUIRED_COLUMNS as $col => $label) {
            if (! in_array($col, $presentCols, true)) {
                $missing[] = "\"$label\"";
            }
        }

        if (! empty($missing)) {
            $missingList = implode(', ', $missing);
            return "Format file tidak sesuai template. Kolom yang tidak ditemukan: {$missingList}. "
                . "Pastikan Anda menggunakan template yang sudah disediakan dan tidak mengubah nama header kolom.";
        }

        return null;
    }

    /**
     * Map jenis_pengisian dari nilai Excel ke nilai database.
     */
    private function mapJenisPengisian(?string $raw): string
    {
        $val = strtolower(trim((string) $raw));

        if ($val === '' || $val === 'operational' || $val === 'operasional') {
            return BbmReport::JENIS_PENGISIAN_OPERASIONAL;
        }

        if ($val === 'sppd' || $val === 'perjalanan dinas (sppd)' || $val === 'perjalanan dinas') {
            return BbmReport::JENIS_PENGISIAN_PERJALANAN_DINAS;
        }

        return BbmReport::JENIS_PENGISIAN_OPERASIONAL;
    }

    /**
     * Map shift label ke kode shift.
     */
    private function mapShift(?string $raw): string
    {
        return match (strtolower(trim((string) $raw))) {
            'pagi'  => 'pagi',
            'siang' => 'siang',
            default => 'luar',
        };
    }

    /**
     * Parse tanggal dari berbagai format, termasuk Excel serial number.
     */
    private function parseTanggal(mixed $raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        // Excel serial number
        if (is_numeric($raw) && (float) $raw > 1000) {
            try {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $raw);
                return Carbon::instance($date)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        // String — coba berbagai format umum Indonesia
        $formats = ['d-m-Y', 'd/m/Y', 'Y-m-d', 'd-m-y', 'd/m/y', 'j/n/Y', 'j-n-Y', 'Y/m/d'];
        foreach ($formats as $fmt) {
            try {
                $d = Carbon::createFromFormat($fmt, trim((string) $raw));
                if ($d !== false) {
                    return $d->format('Y-m-d');
                }
            } catch (\Throwable) {
                // lanjut
            }
        }

        try {
            return Carbon::parse((string) $raw)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Parse waktu ke format H:i.
     */
    private function parseWaktu(mixed $raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        // Excel time serial fraction (< 1)
        if (is_numeric($raw) && (float) $raw < 1) {
            try {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $raw);
                return Carbon::instance($date)->format('H:i');
            } catch (\Throwable) {
                return null;
            }
        }

        $str = trim((string) $raw);

        if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $str)) {
            return substr($str, 0, 5);
        }

        try {
            return Carbon::parse($str)->format('H:i');
        } catch (\Throwable) {
            return null;
        }
    }

    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            $this->fatalError = 'File Excel kosong atau tidak mengandung data. '
                . 'Pastikan file menggunakan template yang benar dan sudah diisi dengan data.';
            return;
        }

        // Validasi kolom berdasarkan baris pertama data
        $firstRow = $rows->first()->toArray();
        $columnError = $this->validateColumns($firstRow);
        if ($columnError !== null) {
            $this->fatalError = $columnError;
            return;
        }

        $rowNumber = 1; // heading = baris 1, data mulai dari baris 2

        foreach ($rows as $row) {
            $rowNumber++;
            $rowArr = $row->toArray();

            $username       = trim((string) ($rowArr['username'] ?? ''));
            $nomorPolisi    = trim((string) ($rowArr['nomor_polisi'] ?? $rowArr['nomor_kendaraan'] ?? ''));
            $jenisPengisian = trim((string) ($rowArr['jenis_pengisian'] ?? ''));
            $tanggalRaw     = $rowArr['tanggal'] ?? null;
            $waktuRaw       = $rowArr['waktu'] ?? null;
            $shiftRaw       = trim((string) ($rowArr['shift'] ?? ''));
            $kmSebelum      = $rowArr['km_sebelum'] ?? null;
            $kmSesudah      = $rowArr['km_sesudah'] ?? null;
            $volume         = $rowArr['volume'] ?? $rowArr['volume_liter'] ?? null;
            $harga          = $rowArr['harga'] ?? $rowArr['harga_per_liter'] ?? null;
            $totalHarga     = $rowArr['total_harga'] ?? null;
            $fotoOdometer   = trim((string) ($rowArr['foto_odometer'] ?? ''));
            $fotoStruk      = trim((string) ($rowArr['foto_struk'] ?? ''));

            // Skip baris yang benar-benar kosong
            if ($username === '' && $nomorPolisi === '' && $tanggalRaw === null) {
                $this->skippedCount++;
                continue;
            }

            $errors = [];

            // ── Username ──────────────────────────────
            if ($username === '') {
                $errors[] = 'Kolom "username" kosong. Isi dengan username pengemudi yang terdaftar di sistem.';
            } else {
                $user = User::where('username', $username)->first();
                if (! $user) {
                    $errors[] = "Username \"{$username}\" tidak ditemukan di sistem. "
                        . 'Pastikan username sudah terdaftar dan penulisannya benar (huruf kecil, tanpa spasi).';
                }
            }

            // ── Nomor Polisi ──────────────────────────
            if ($nomorPolisi === '') {
                $errors[] = 'Kolom "nomor_polisi" kosong. Isi dengan nomor plat kendaraan (contoh: B 1234 ABC).';
            } else {
                $kendaraan = Kendaraan::where('nomor_kendaraan', $nomorPolisi)->first();
                if (! $kendaraan) {
                    $errors[] = "Nomor kendaraan \"{$nomorPolisi}\" tidak ditemukan di master armada. "
                        . 'Pastikan nomor plat sesuai persis dengan data kendaraan yang ada di sistem.';
                }
            }

            // ── Tanggal ───────────────────────────────
            $tanggal = $this->parseTanggal($tanggalRaw);
            if (! $tanggal) {
                $rawStr = $tanggalRaw !== null ? "\"{$tanggalRaw}\"" : '(kosong)';
                $errors[] = "Tanggal {$rawStr} tidak dapat dibaca. "
                    . 'Gunakan format DD-MM-YYYY (contoh: 14-07-2026) atau biarkan Excel otomatis mengenali sebagai tanggal.';
            }

            // ── Waktu ─────────────────────────────────
            $waktu = $this->parseWaktu($waktuRaw);
            if (! $waktu) {
                $rawStr = $waktuRaw !== null ? "\"{$waktuRaw}\"" : '(kosong)';
                $errors[] = "Waktu {$rawStr} tidak valid. "
                    . 'Gunakan format HH:MM (contoh: 08:30).';
            }

            // ── KM ────────────────────────────────────
            $kmSebelumInt = is_numeric($kmSebelum) ? (int) $kmSebelum : null;
            $kmSesudahInt = is_numeric($kmSesudah) ? (int) $kmSesudah : null;

            if ($kmSebelumInt === null) {
                $rawStr = $kmSebelum !== null ? "\"{$kmSebelum}\"" : '(kosong)';
                $errors[] = "KM Sebelum {$rawStr} bukan angka. Isi dengan angka bulat (contoh: 45000).";
            }
            if ($kmSesudahInt === null) {
                $rawStr = $kmSesudah !== null ? "\"{$kmSesudah}\"" : '(kosong)';
                $errors[] = "KM Sesudah {$rawStr} bukan angka. Isi dengan angka bulat (contoh: 45120).";
            }
            if ($kmSebelumInt !== null && $kmSesudahInt !== null && $kmSesudahInt < $kmSebelumInt) {
                $errors[] = "KM Sesudah ({$kmSesudahInt}) tidak boleh lebih kecil dari KM Sebelum ({$kmSebelumInt}).";
            }

            // ── Volume & Harga ────────────────────────
            $volumeFloat = is_numeric($volume) ? (float) $volume : null;
            $hargaFloat  = is_numeric($harga)  ? (float) $harga  : null;

            if ($volumeFloat === null || $volumeFloat <= 0) {
                $rawStr = $volume !== null ? "\"{$volume}\"" : '(kosong)';
                $errors[] = "Volume/liter {$rawStr} tidak valid. Isi dengan angka positif (contoh: 40).";
            }
            if ($hargaFloat === null || $hargaFloat < 0) {
                $rawStr = $harga !== null ? "\"{$harga}\"" : '(kosong)';
                $errors[] = "Harga per liter {$rawStr} tidak valid. Isi dengan angka positif (contoh: 13500).";
            }

            if (! empty($errors)) {
                $this->importErrors[] = ['row' => $rowNumber, 'errors' => $errors];
                $this->skippedCount++;
                continue;
            }

            // ── Simpan ke database ────────────────────
            /** @var User $user */
            /** @var Kendaraan $kendaraan */
            $totalHargaFloat = is_numeric($totalHarga) ? (float) $totalHarga : null;
            $computedTotal   = ($totalHargaFloat !== null && $totalHargaFloat > 0)
                ? $totalHargaFloat
                : round((float) $volumeFloat * (float) $hargaFloat, 2);

            $odoPath   = $fotoOdometer !== '' ? 'bbm-reports/odometer/' . $fotoOdometer : null;
            $strukPath = $fotoStruk    !== '' ? 'bbm-reports/struk/'    . $fotoStruk    : null;

            BbmReport::create([
                'user_id'             => $user->id,
                'kendaraan_id'        => $kendaraan->id,
                'nomor_kendaraan'     => $kendaraan->nomor_kendaraan,
                'jenis_kendaraan'     => $kendaraan->jenis_kendaraan,
                'jenis_pengisian'     => $this->mapJenisPengisian($jenisPengisian),
                'tanggal'             => $tanggal,
                'waktu'               => $waktu,
                'shift'               => $this->mapShift($shiftRaw),
                'odometer_sebelum'    => $kmSebelumInt,
                'odometer_sesudah'    => $kmSesudahInt,
                'liter'               => $volumeFloat,
                'harga_per_liter'     => $hargaFloat,
                'total_harga'         => $computedTotal,
                'odometer_photo_path' => $odoPath,
                'struk_photo_path'    => $strukPath,
            ]);

            $this->successCount++;
        }
    }

    public function rules(): array
    {
        return [];
    }

    /** @return string|null */
    public function getFatalError(): ?string
    {
        return $this->fatalError;
    }

    /** @return list<array{row: int, errors: list<string>}> */
    public function getImportErrors(): array
    {
        $traitErrors = [];
        foreach ($this->failures() as $failure) {
            $traitErrors[] = [
                'row'    => $failure->row(),
                'errors' => $failure->errors(),
            ];
        }

        return array_merge($traitErrors, $this->importErrors);
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }
}
