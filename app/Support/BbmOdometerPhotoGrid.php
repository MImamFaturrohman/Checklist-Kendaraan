<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Menggabungkan dua foto odometer (sebelum + sesudah) menjadi satu gambar grid
 * vertikal (atas-bawah) menggunakan PHP GD.
 *
 * Label "SEBELUM" / "SESUDAH" digambar sebagai overlay pill proporsional di pojok
 * kiri atas masing-masing foto.  Teknik rendering:
 *   – Pill digambar di canvas terpisah dengan warna SOLID (tanpa alpha per-pixel)
 *     agar tidak ada compounding opacity di area overlap antar shape.
 *   – Teks di-scale-up dari font GD bawaan menggunakan imagescale(), dengan
 *     background canvas mini berwarna navy (bukan hitam) supaya tidak ada
 *     artefak hitam di sekitar huruf.
 *   – Efek semi-transparan diperoleh satu kali di akhir via imagecopymerge().
 */
class BbmOdometerPhotoGrid
{
    /** Lebar maksimal tiap sel foto (px). */
    private const MAX_CELL_WIDTH = 960;

    /** Tinggi maksimal tiap sel foto (px). */
    private const MAX_CELL_HEIGHT = 720;

    /** Celah (gap) antara dua foto (px). */
    private const GAP = 6;

    /** Font GD bawaan yang dipakai sebagai sumber bitmap sebelum di-scale (font 5 = 9×15 px). */
    private const LABEL_FONT = 5;

    /** Kualitas output JPEG (0–100). */
    private const JPEG_QUALITY = 85;

    // ── Proporsi pill label (terhadap lebar canvas) ───────────────────────────
    /** Tinggi pill = canvasW × rasio ini; minimal PILL_H_MIN px. */
    private const PILL_H_RATIO = 0.048;
    private const PILL_H_MIN   = 32;

    /** Padding horizontal dalam pill (× pillH). */
    private const PILL_PAD_X = 0.50;

    /** Padding vertikal dalam pill (× pillH). */
    private const PILL_PAD_Y = 0.20;

    /** Corner radius (× pillH). — 0.5 = capsule penuh */
    private const PILL_RADIUS = 0.38;

    /** Opasitas pill saat di-merge ke foto (0–100). */
    private const PILL_OPACITY = 78;

    /** Offset pill dari sudut foto (× lebar sel foto), minimal PILL_OFF_MIN px. */
    private const PILL_OFF_RATIO = 0.012;
    private const PILL_OFF_MIN   = 10;

    // ── Warna ────────────────────────────────────────────────────────────────
    private const C_NAVY_R   = 11;
    private const C_NAVY_G   = 44;
    private const C_NAVY_B   = 107;
    private const C_YELLOW_R = 250;
    private const C_YELLOW_G = 204;
    private const C_YELLOW_B = 21;

    /* =========================================================================
     *  PUBLIC
     * ======================================================================= */

    public static function compose(UploadedFile $sebelum, UploadedFile $sesudah): string
    {
        $src1 = imagecreatefromstring(file_get_contents($sebelum->getRealPath()));
        $src2 = imagecreatefromstring(file_get_contents($sesudah->getRealPath()));

        [$w1, $h1] = self::scaleDimensions(imagesx($src1), imagesy($src1));
        [$w2, $h2] = self::scaleDimensions(imagesx($src2), imagesy($src2));

        $cW = max($w1, $w2);
        $cH = $h1 + self::GAP + $h2;

        $canvas = imagecreatetruecolor($cW, $cH);
        $bgCol  = imagecolorallocate($canvas, 20, 30, 48);
        imagefilledrectangle($canvas, 0, 0, $cW - 1, $cH - 1, $bgCol);

        // ── Foto 1 ────────────────────────────────────────────────────────────
        $x1 = (int)(($cW - $w1) / 2);
        $s1 = imagecreatetruecolor($w1, $h1);
        imagecopyresampled($s1, $src1, 0, 0, 0, 0, $w1, $h1, imagesx($src1), imagesy($src1));
        imagecopy($canvas, $s1, $x1, 0, 0, 0, $w1, $h1);
        imagedestroy($s1);
        imagedestroy($src1);

        $off1 = max(self::PILL_OFF_MIN, (int)round($w1 * self::PILL_OFF_RATIO));
        self::stampPill($canvas, $x1 + $off1, $off1, 'SEBELUM', $cW);

        // ── Foto 2 ────────────────────────────────────────────────────────────
        $y2 = $h1 + self::GAP;
        $x2 = (int)(($cW - $w2) / 2);
        $s2 = imagecreatetruecolor($w2, $h2);
        imagecopyresampled($s2, $src2, 0, 0, 0, 0, $w2, $h2, imagesx($src2), imagesy($src2));
        imagecopy($canvas, $s2, $x2, $y2, 0, 0, $w2, $h2);
        imagedestroy($s2);
        imagedestroy($src2);

        $off2 = max(self::PILL_OFF_MIN, (int)round($w2 * self::PILL_OFF_RATIO));
        self::stampPill($canvas, $x2 + $off2, $y2 + $off2, 'SESUDAH', $cW);

        // ── Output ────────────────────────────────────────────────────────────
        ob_start();
        imagejpeg($canvas, null, self::JPEG_QUALITY);
        $jpeg = ob_get_clean();
        imagedestroy($canvas);

        $path = 'bbm-reports/odometer/' . Str::uuid() . '.jpg';
        Storage::disk('public')->put($path, $jpeg);

        return $path;
    }

    /* =========================================================================
     *  PRIVATE
     * ======================================================================= */

    /**
     * Gambar pill overlay berteks ke $canvas pada koordinat ($px, $py).
     *
     * Alur:
     *   1. Buat canvas pill SOLID (opaque penuh) — hindari alpha-compounding.
     *   2. Gambar rounded-rect dengan navy.
     *   3. Scale-up teks dari canvas mini ber-background navy (hilangkan kotak hitam).
     *   4. Tempel ke canvas utama sekali pakai imagecopymerge() → semi-transparan.
     */
    private static function stampPill(
        \GdImage $canvas,
        int      $px,
        int      $py,
        string   $text,
        int      $canvasW,
    ): void {
        // ── 1. Hitung dimensi ─────────────────────────────────────────────────
        $pH = max(self::PILL_H_MIN, (int)round($canvasW * self::PILL_H_RATIO));
        $padX = (int)round($pH * self::PILL_PAD_X);
        $padY = (int)round($pH * self::PILL_PAD_Y);
        $r    = (int)round($pH * self::PILL_RADIUS);

        $gdCW  = imagefontwidth(self::LABEL_FONT);
        $gdCH  = imagefontheight(self::LABEL_FONT);
        $iH    = $pH - $padY * 2;                          // tinggi area teks
        $ratio = $iH / $gdCH;
        $sCW   = max(1, (int)round($gdCW * $ratio));       // char width setelah scale
        $iW    = $sCW * strlen($text);                     // lebar area teks
        $pW    = $iW + $padX * 2;                          // lebar pill

        // ── 2. Canvas pill (solid, tanpa alpha) ───────────────────────────────
        $pill = imagecreatetruecolor($pW, $pH);

        // Warna "luar" (sudut bounding-box di luar pill) — gunakan warna foto sehingga
        // saat imagecopymerge tidak ada darkening, tapi karena posisinya di luar
        // corner tidak signifikan.  Kita pakai warna transparan palsu = fill dgn
        // warna yang sama dengan navy agar sudut tidak terlihat hitam setelah merge.
        // Trik: isi keseluruhan dengan navy dahulu; lalu fill "sudut" dengan
        // warna berbeda.  Karena bounding-box pill = pW × pH dan corner area sangat
        // kecil (r ≈ 38 % pH), gunakan solid navy di seluruh area → tidak ada
        // artefak sama sekali karena imagecopymerge mengorbankan corner transparan.
        //
        // Solusi bersih: fill entire canvas dengan navy (bukan hitam), sehingga
        // sudut bounding-box pun berwarna navy — setelah imagecopymerge efeknya
        // identik dengan area pill itu sendiri, tanpa darkening.
        $navy   = imagecolorallocate($pill, self::C_NAVY_R, self::C_NAVY_G, self::C_NAVY_B);
        $yellow = imagecolorallocate($pill, self::C_YELLOW_R, self::C_YELLOW_G, self::C_YELLOW_B);

        // Fill seluruh canvas dengan navy (termasuk "sudut" luar rounded-rect)
        imagefilledrectangle($pill, 0, 0, $pW - 1, $pH - 1, $navy);

        // Rounded corners: "potong" sudut dengan navy tetap → pill sudah terbentuk
        // karena seluruh canvas sudah navy, rounded-rect hanya dekorasi.
        // (Kita skip gambar rounded-rect karena canvas sudah navy uniform.)
        //
        // Jika ingin corner lebih terlihat, uncomment blok di bawah:
        // (tidak perlu karena entire canvas sudah navy)

        // ── 3. Teks: mini canvas ber-bg navy → scale → tempel ke pill ─────────
        $srcW = $gdCW * strlen($text);
        $srcH = $gdCH;

        $mini  = imagecreatetruecolor($srcW, $srcH);
        $mNavy = imagecolorallocate($mini, self::C_NAVY_R, self::C_NAVY_G, self::C_NAVY_B);
        $mYell = imagecolorallocate($mini, self::C_YELLOW_R, self::C_YELLOW_G, self::C_YELLOW_B);
        imagefilledrectangle($mini, 0, 0, $srcW - 1, $srcH - 1, $mNavy);
        imagestring($mini, self::LABEL_FONT, 0, 0, $text, $mYell);

        $big = imagescale($mini, $iW, $iH, IMG_BILINEAR_FIXED);
        imagedestroy($mini);

        if ($big !== false) {
            imagecopy($pill, $big, $padX, $padY, 0, 0, $iW, $iH);
            imagedestroy($big);
        } else {
            // fallback: tulis langsung tanpa scale
            imagestring($pill, self::LABEL_FONT, $padX, $padY, $text, $yellow);
        }

        // ── 4. Merge semi-transparan ke canvas utama ──────────────────────────
        // imagecopymerge() mengabaikan alpha source, tapi karena seluruh $pill
        // berwarna navy (bukan hitam), tidak ada corner darkening.
        imagecopymerge($canvas, $pill, $px, $py, 0, 0, $pW, $pH, self::PILL_OPACITY);
        imagedestroy($pill);
    }

    /**
     * Skala foto agar muat dalam MAX_CELL_WIDTH × MAX_CELL_HEIGHT (contain, ratio asli).
     *
     * @return array{int, int}
     */
    private static function scaleDimensions(int $srcW, int $srcH): array
    {
        if ($srcW <= 0 || $srcH <= 0) {
            return [1, 1];
        }

        $scale = min(
            self::MAX_CELL_WIDTH  / $srcW,
            self::MAX_CELL_HEIGHT / $srcH,
            1.0,
        );

        return [
            max(1, (int)round($srcW * $scale)),
            max(1, (int)round($srcH * $scale)),
        ];
    }
}
