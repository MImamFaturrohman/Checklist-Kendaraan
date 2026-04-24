<?php

namespace Database\Seeders;

use App\Models\Bidang;
use App\Models\Pernyataan;
use Illuminate\Database\Seeder;

class BidangPernyataanSeeder extends Seeder
{
    public function run(): void
    {
        $pm = Bidang::query()->firstOrCreate(
            ['parent_id' => null, 'nama' => 'PM SLA'],
            ['sort_order' => 0]
        );

        $dir = Bidang::query()->firstOrCreate(
            ['parent_id' => null, 'nama' => 'DIREKTORAT OPERASI KANTOR PUSAT'],
            ['sort_order' => 1]
        );

        $pmChildren = [
            'HSE',
            'Sub Bidang Jetty Master',
            'Sub Bidang Operasi',
            'Sub Bidang Teknik',
            'Bagian Keuangan & Administrasi',
        ];

        foreach ($pmChildren as $i => $nama) {
            Bidang::query()->firstOrCreate(
                ['parent_id' => $pm->id, 'nama' => $nama],
                ['sort_order' => $i + 1]
            );
        }

        Bidang::query()->firstOrCreate(
            ['parent_id' => $dir->id, 'nama' => 'Bidang Operasi'],
            ['sort_order' => 1]
        );

        $pernyataanTexts = [
            'Memperbaiki dan menanggung biaya perbaikan bila terjadi kerusakan pada kendaraan',
            'Memberikan penggantian kendaraan jika kendaraan hilang (dengan spesifikasi kendaraan yang sama)',
            'Menyediakan kendaraan pengganti untuk operasional kantor ADC PM SLA selama kendaraan sedang dalam perbaikan, jika kendaraan mengalami kerusakan',
            'Mengisi ulang bahan bakar yang terpakai',
        ];

        foreach ($pernyataanTexts as $i => $teks) {
            Pernyataan::query()->updateOrCreate(
                ['urutan' => $i + 1],
                ['isi_pernyataan' => $teks]
            );
        }
    }
}
