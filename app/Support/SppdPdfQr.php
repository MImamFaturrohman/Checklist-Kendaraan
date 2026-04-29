<?php

namespace App\Support;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

final class SppdPdfQr
{
    /**
     * Ukuran bitmap QR dalam px (disarankan besar agar tetap tajam saat diskalakan di PDF).
     * Tampilan akhir mengikuti lebar kolom CSS (.sig-qr-wrap).
     */
    public static function pngDataUri(string $text, int $size = 280): string
    {
        $text = trim($text) === '' ? '—' : trim($text);
        $qr = QrCode::create($text)->setSize($size)->setMargin(2);

        return (new PngWriter())->write($qr)->getDataUri();
    }
}
