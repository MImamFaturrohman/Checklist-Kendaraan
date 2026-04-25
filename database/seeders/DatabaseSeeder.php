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
        $this->call(SuperAdminSeeder::class);

        // =========================
        // USER SEEDER
        // =========================

        User::firstOrCreate(
            ['username' => 'manager'],
            [
                'name' => 'Manager Pusat',
                'email' => 'manager@arthadaya.com',
                'password' => Hash::make('password'),
                'role' => 'manager',
            ]
        );

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
            ['nomor_kendaraan' => 'B 2784 PZU', 'jenis_kendaraan' => 'MITSUBISHI XPANDER', 'set_km' => 3252],
            ['nomor_kendaraan' => 'B 9394 PAO', 'jenis_kendaraan' => 'TOYOTA HILUX PICK UP', 'set_km' => 2931],
            ['nomor_kendaraan' => 'B 9396 PAO', 'jenis_kendaraan' => 'TOYOTA HILUX PICK UP', 'set_km' => 1234],
            ['nomor_kendaraan' => 'B 9398 PAO', 'jenis_kendaraan' => 'TOYOTA HILUX PICK UP', 'set_km' => 5678],
            ['nomor_kendaraan' => 'B 9400 PAO', 'jenis_kendaraan' => 'TOYOTA HILUX PICK UP', 'set_km' => 9012],
            ['nomor_kendaraan' => 'B 9402 PAO', 'jenis_kendaraan' => 'TOYOTA HILUX PICK UP', 'set_km' => 3456],
            ['nomor_kendaraan' => 'B 9458 PAO', 'jenis_kendaraan' => 'DAIHATSU GRAN MAX PICK UP', 'set_km' => 7890],
        ];

        foreach ($kendaraans as $k) {
            Kendaraan::firstOrCreate(
                ['nomor_kendaraan' => $k['nomor_kendaraan']], // 🔑 unik
                [
                    'jenis_kendaraan' => $k['jenis_kendaraan'],
                    'set_km' => $k['set_km'],
                ]
            );
        }

        $this->call(BidangPernyataanSeeder::class);
    }
}
