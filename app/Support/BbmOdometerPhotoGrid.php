<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Menggabungkan dua foto odometer (sebelum + sesudah) menjadi satu gambar grid
 * vertikal (atas-bawah) menggunakan PHP GD, tanpa menyimpan file original ke storage.
 * Aspect ratio tiap foto dipertahankan; ukuran dinormalisasi ke bounding box.
 */
class BbmOdometerPhotoGrid
{
    /** Lebar maksimal tiap sel foto (px). */
    private const MAX_CELL_WIDTH = 960;

    /** Tinggi maksimal tiap sel foto (px) — cegah portrait extreme tall. */
    private const MAX_CELL_HEIGHT = 720;

    /** Celah antar dua foto (px). */
    private const GAP = 8;

    /** Font GD bawaan untuk label (5 = lebih besar dari 4). */
    private const LABEL_FONT = 5;

    /** Kualitas output JPEG (0–100). */
    private const JPEG_QUALITY = 85;

    /**
     * Gabungkan dua foto menjadi satu grid vertikal (Sebelum atas, Sesudah bawah),
     * simpan ke storage public, dan kembalikan path relatif (odometer_photo_path).
     */
    public static function compose(UploadedFile $sebelum, UploadedFile $sesudah): string
    {
        $src1 = imagecreatefromstring(file_get_contents($sebelum->getRealPath()));
        $src2 = imagecreatefromstring(file_get_contents($sesudah->getRealPath()));

        [$w1s, $h1s] = self::scaleDimensions(imagesx($src1), imagesy($src1));
        [$w2s, $h2s] = self::scaleDimensions(imagesx($src2), imagesy($src2));

        $canvasW = max($w1s, $w2s);
        $labelH  = self::labelHeight($canvasW);
        $canvasH = ($labelH + $h1s) + self::GAP + ($labelH + $h2s);

        $canvas = imagecreatetruecolor($canvasW, $canvasH);

        $bgColor   = imagecolorallocate($canvas, 20, 30, 48);
        $labelBg   = imagecolorallocate($canvas, 11, 44, 107);
        $textColor = imagecolorallocate($canvas, 250, 204, 21);

        imagefilledrectangle($canvas, 0, 0, $canvasW - 1, $canvasH - 1, $bgColor);

        // Baris 1: Sebelum
        $y1Image = self::drawLabelBar($canvas, 0, $canvasW, $labelH, $labelBg, $textColor, 'Sebelum');

        $scaled1 = imagecreatetruecolor($w1s, $h1s);
        imagecopyresampled($scaled1, $src1, 0, 0, 0, 0, $w1s, $h1s, imagesx($src1), imagesy($src1));
        imagecopy($canvas, $scaled1, (int) (($canvasW - $w1s) / 2), $y1Image, 0, 0, $w1s, $h1s);
        imagedestroy($scaled1);
        imagedestroy($src1);

        // Baris 2: Sesudah
        $y2Label = $y1Image + $h1s + self::GAP;
        $y2Image = self::drawLabelBar($canvas, $y2Label, $canvasW, $labelH, $labelBg, $textColor, 'Sesudah');

        $scaled2 = imagecreatetruecolor($w2s, $h2s);
        imagecopyresampled($scaled2, $src2, 0, 0, 0, 0, $w2s, $h2s, imagesx($src2), imagesy($src2));
        imagecopy($canvas, $scaled2, (int) (($canvasW - $w2s) / 2), $y2Image, 0, 0, $w2s, $h2s);
        imagedestroy($scaled2);
        imagedestroy($src2);

        ob_start();
        imagejpeg($canvas, null, self::JPEG_QUALITY);
        $jpeg = ob_get_clean();
        imagedestroy($canvas);

        $path = 'bbm-reports/odometer/' . Str::uuid() . '.jpg';
        Storage::disk('public')->put($path, $jpeg);

        return $path;
    }

    /** Tinggi bar label ~4.5% lebar canvas, minimum 36px. */
    private static function labelHeight(int $canvasW): int
    {
        return max(36, (int) round($canvasW * 0.045));
    }

    /**
     * Gambar bar label full-width; kembalikan Y awal area foto di bawah label.
     */
    private static function drawLabelBar(
        \GdImage $canvas,
        int $y,
        int $canvasW,
        int $labelH,
        int $labelBg,
        int $textColor,
        string $text,
    ): int {
        imagefilledrectangle($canvas, 0, $y, $canvasW - 1, $y + $labelH - 1, $labelBg);

        $charW = imagefontwidth(self::LABEL_FONT);
        $charH = imagefontheight(self::LABEL_FONT);
        $textX = (int) max(4, ($canvasW - strlen($text) * $charW) / 2);
        $textY = $y + (int) (($labelH - $charH) / 2);

        imagestring($canvas, self::LABEL_FONT, $textX, $textY, $text, $textColor);

        return $y + $labelH;
    }

    /**
     * Skala foto agar muat dalam MAX_CELL_WIDTH × MAX_CELL_HEIGHT (contain, ratio asli).
     *
     * @return array{int, int}  [$scaledW, $scaledH]
     */
    private static function scaleDimensions(int $srcW, int $srcH): array
    {
        if ($srcW <= 0 || $srcH <= 0) {
            return [1, 1];
        }

        $scale = min(
            self::MAX_CELL_WIDTH / $srcW,
            self::MAX_CELL_HEIGHT / $srcH,
            1.0,
        );

        return [
            max(1, (int) round($srcW * $scale)),
            max(1, (int) round($srcH * $scale)),
        ];
    }
}
