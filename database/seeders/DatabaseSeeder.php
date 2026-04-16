<?php

namespace Database\Seeders;

use App\Models\Kendaraan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@arthadaya.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create driver user
        User::create([
            'name' => 'M. Rizcky DT',
            'username' => 'rizcky',
            'email' => 'rizcky@arthadaya.com',
            'password' => Hash::make('password'),
            'role' => 'driver',
        ]);

        User::create([
            'name' => 'Hasan Nawawi',
            'username' => 'hasan',
            'email' => 'hasan@arthadaya.com',
            'password' => Hash::make('password'),
            'role' => 'driver',
        ]);

        // Seed kendaraan master data
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
            Kendaraan::create($k);
        }
    }
}
