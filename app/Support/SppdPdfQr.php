<?php

namespace App\Support;

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

final class SppdPdfQr
{
    /**
     * Ukuran bitmap QR dalam px. Logo ADC dibuat bulat; tanpa punch-out persegi agar pola QR tetap terlihat di luar lingkaran logo (PDF tidak menampilkan artefak kotak hitam).
     *
     * @param  string|null  $logoPath  Path PNG logo di tengah QR (default: public/images/ADC.png)
     */
    public static function pngDataUri(string $text, int $size = 280, ?string $logoPath = null): string
    {
        $text = trim($text) === '' ? '—' : trim($text);

        $qr = QrCode::create($text)
            ->setSize($size)
            ->setMargin(2)
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::High);

        $writer = new PngWriter();

        $logoPath ??= dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'ADC.png';

        if (! is_readable($logoPath)) {
            return $writer->write($qr)->getDataUri();
        }

        $logoWidth = max(36, (int) round($size * 0.22));

        $tmpCircular = self::buildCircularLogoFile($logoPath, $logoWidth);

        try {
            $logoFile = $tmpCircular ?? $logoPath;

            // false: tanpa “punch out” persegi — DOM PDF sering merender transparansi sebagai kotak hitam;
            // pola QR tetap di bawah; logo bundar ber-alpha menyatu di atas (area transparan menampilkan modul QR).
            $logo = Logo::create($logoFile)
                ->setResizeToWidth($logoWidth)
                ->setPunchoutBackground(false);

            return $writer->write($qr, $logo)->getDataUri();
        } finally {
            if ($tmpCircular !== null && is_file($tmpCircular)) {
                @unlink($tmpCircular);
            }
        }
    }

    /**
     * Crop persegi dari pusat, skala ke $pixelSize, lalu mask lingkaran (PNG dengan alpha).
     */
    private static function buildCircularLogoFile(string $sourcePath, int $pixelSize): ?string
    {
        if ($pixelSize < 8 || ! extension_loaded('gd')) {
            return null;
        }

        $data = @file_get_contents($sourcePath);
        if ($data === false) {
            return null;
        }

        $src = @imagecreatefromstring($data);
        if ($src === false) {
            return null;
        }

        $w = imagesx($src);
        $h = imagesy($src);
        $side = min($w, $h);
        $offX = (int) (($w - $side) / 2);
        $offY = (int) (($h - $side) / 2);

        $square = imagecrop($src, ['x' => $offX, 'y' => $offY, 'width' => $side, 'height' => $side]);
        imagedestroy($src);

        if ($square === false) {
            return null;
        }

        $scaled = imagescale($square, $pixelSize, $pixelSize);
        imagedestroy($square);

        if ($scaled === false) {
            return null;
        }

        $dst = imagecreatetruecolor($pixelSize, $pixelSize);
        imagesavealpha($dst, true);
        imagealphablending($dst, false);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefill($dst, 0, 0, $transparent);
        imagealphablending($dst, true);

        $r = ($pixelSize / 2) - 0.5;
        $cx = ($pixelSize / 2) - 0.5;
        $cy = ($pixelSize / 2) - 0.5;

        for ($y = 0; $y < $pixelSize; $y++) {
            for ($x = 0; $x < $pixelSize; $x++) {
                $dx = $x - $cx;
                $dy = $y - $cy;
                if (($dx * $dx + $dy * $dy) <= ($r * $r)) {
                    imagesetpixel($dst, $x, $y, imagecolorat($scaled, $x, $y));
                }
            }
        }

        imagedestroy($scaled);

        $tmp = sys_get_temp_dir().DIRECTORY_SEPARATOR.'sppd_qr_logo_'.uniqid('', true).'.png';
        imagepng($dst, $tmp);
        imagedestroy($dst);

        return is_file($tmp) ? $tmp : null;
    }
}
