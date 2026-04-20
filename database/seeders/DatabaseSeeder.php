<?php

namespace Database\Seeders;

use App\Models\Kendaraan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // =========================
        // USER SEEDER
        // =========================

        User::firstOrCreate(
            ['username' => 'admin'], // 🔑 kondisi unik
            [
                'name' => 'Administrator',
                'email' => 'admin@arthadaya.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        User::firstOrCreate(
            ['username' => 'rizcky'],
            [
                'name' => 'M. Rizcky DT',
                'email' => 'rizcky@arthadaya.com',
                'password' => Hash::make('password'),
                'role' => 'driver',
            ]
        );

        User::firstOrCreate(
            ['username' => 'hasan'],
            [
                'name' => 'Hasan Nawawi',
                'email' => 'hasan@arthadaya.com',
                'password' => Hash::make('password'),
                'role' => 'driver',
            ]
        );

        // =========================
        // KENDARAAN SEEDER
        // =========================

        $kendaraans = [
            ['nomor_kendaraan' => 'B 2784 PZU', 'jenis_kendaraan' => 'MITSUBISHI XPANDER'],
            ['nomor_kendaraan' => 'B 1234 ABC', 'jenis_kendaraan' => 'TOYOTA AVANZA'],
            ['nomor_kendaraan' => 'B 5678 DEF', 'jenis_kendaraan' => 'HINO DUTRO'],
            ['nomor_kendaraan' => 'B 9012 GHI', 'jenis_kendaraan' => 'ISUZU ELF'],
            ['nomor_kendaraan' => 'B 3456 JKL', 'jenis_kendaraan' => 'MITSUBISHI CANTER'],
            ['nomor_kendaraan' => 'B 7890 MNO', 'jenis_kendaraan' => 'TOYOTA HILUX'],
            ['nomor_kendaraan' => 'B 2345 PQR', 'jenis_kendaraan' => 'SUZUKI CARRY'],
            ['nomor_kendaraan' => 'B 6789 STU', 'jenis_kendaraan' => 'DAIHATSU GRAN MAX'],
        ];

        foreach ($kendaraans as $k) {
            Kendaraan::firstOrCreate(
                ['nomor_kendaraan' => $k['nomor_kendaraan']], // 🔑 unik
                [
                    'jenis_kendaraan' => $k['jenis_kendaraan'],
                ]
            );
        }
    }
}