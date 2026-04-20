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
                'name' => 'Admin Pusat',
                'email' => 'admin@arthadaya.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        User::firstOrCreate(
            ['username' => 'rizky'],
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
            ['nomor_kendaraan' => 'B 2784 PZU', 'jenis_kendaraan' => 'MITSUBISHI XPANDER', 'km_awal' => 3252],
            ['nomor_kendaraan' => 'B 9394 PAO', 'jenis_kendaraan' => 'TOYOTA HILUX PICK UP', 'km_awal' => 2931],
            ['nomor_kendaraan' => 'B 9396 PAO', 'jenis_kendaraan' => 'TOYOTA HILUX PICK UP', 'km_awal' => 1234],
            ['nomor_kendaraan' => 'B 9398 PAO', 'jenis_kendaraan' => 'TOYOTA HILUX PICK UP', 'km_awal' => 5678],
            ['nomor_kendaraan' => 'B 9400 PAO', 'jenis_kendaraan' => 'TOYOTA HILUX PICK UP', 'km_awal' => 9012],
            ['nomor_kendaraan' => 'B 9402 PAO', 'jenis_kendaraan' => 'TOYOTA HILUX PICK UP', 'km_awal' => 3456],
            ['nomor_kendaraan' => 'B 9458 PAO', 'jenis_kendaraan' => 'DAIHATSU GRAN MAX PICK UP', 'km_awal' => 7890],
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