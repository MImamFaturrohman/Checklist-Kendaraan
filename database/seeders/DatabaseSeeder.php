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
                'name' => 'Port Manager',
                'email' => 'manager@arthadaya.com',
                'password' => Hash::make('password'),
                'role' => 'manager',
            ]
        );

        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin',
                'email' => 'admin@arthadaya.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        User::firstOrCreate(
            ['username' => 'rahma'],
            [
                'name' => 'Siti Rahma',
                'email' => 'sitirahma@arthadaya.com',
                'password' => Hash::make('password'),
                'role' => 'pic_kendaraan',
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
            [
                'nomor_kendaraan'   => 'B 2784 PZU',
                'jenis_kendaraan'   => 'MITSUBISHI XPANDER',
                'bidang'            => 'PM SLA',
                'set_km'            => 3252,
                'km_current'        => 3252,
                'tanggal_stnk'      => '2027-03-14',
                'tanggal_pajak_stnk'=> '2026-03-14',
                'tanggal_kir'       => null,
                'status_kendaraan'  => 'Aktif',
            ],
            [
                'nomor_kendaraan'   => 'B 9394 PAO',
                'jenis_kendaraan'   => 'TOYOTA HILUX PICK UP',
                'bidang'            => 'PLN IP ALBES',
                'set_km'            => 2931,
                'km_current'        => 2931,
                'tanggal_stnk'      => '2027-05-21',
                'tanggal_pajak_stnk'=> '2026-05-21',
                'tanggal_kir'       => '2026-11-21',
                'status_kendaraan'  => 'Aktif',
            ],
            [
                'nomor_kendaraan'   => 'B 9396 PAO',
                'jenis_kendaraan'   => 'TOYOTA HILUX PICK UP',
                'bidang'            => 'HSE',
                'set_km'            => 1234,
                'km_current'        => 1234,
                'tanggal_stnk'      => '2027-01-10',
                'tanggal_pajak_stnk'=> '2026-01-10',
                'tanggal_kir'       => '2026-07-10',
                'status_kendaraan'  => 'Aktif',
            ],
            [
                'nomor_kendaraan'   => 'B 9398 PAO',
                'jenis_kendaraan'   => 'TOYOTA HILUX PICK UP',
                'bidang'            => 'SBI TEKNIK',
                'set_km'            => 5678,
                'km_current'        => 5678,
                'tanggal_stnk'      => '2027-02-18',
                'tanggal_pajak_stnk'=> '2026-02-18',
                'tanggal_kir'       => '2026-08-18',
                'status_kendaraan'  => 'Aktif',
            ],
            [
                'nomor_kendaraan'   => 'B 9400 PAO',
                'jenis_kendaraan'   => 'TOYOTA HILUX PICK UP',
                'bidang'            => 'SBI JETTY MASTER',
                'set_km'            => 9012,
                'km_current'        => 9012,
                'tanggal_stnk'      => '2027-04-05',
                'tanggal_pajak_stnk'=> '2026-04-05',
                'tanggal_kir'       => '2026-10-05',
                'status_kendaraan'  => 'Aktif',
            ],
            [
                'nomor_kendaraan'   => 'B 9402 PAO',
                'jenis_kendaraan'   => 'TOYOTA HILUX PICK UP',
                'bidang'            => 'SBI OPERASI',
                'set_km'            => 3456,
                'km_current'        => 3456,
                'tanggal_stnk'      => '2027-06-12',
                'tanggal_pajak_stnk'=> '2026-06-12',
                'tanggal_kir'       => '2026-12-12',
                'status_kendaraan'  => 'Aktif',
            ],
            [
                'nomor_kendaraan'   => 'B 9458 PAO',
                'jenis_kendaraan'   => 'DAIHATSU GRAN MAX PICK UP',
                'bidang'            => 'BIDANG OPERASI ENERGI PRIMER',
                'set_km'            => 7890,
                'km_current'        => 7890,
                'tanggal_stnk'      => '2027-07-30',
                'tanggal_pajak_stnk'=> '2026-07-30',
                'tanggal_kir'       => '2026-09-30',
                'status_kendaraan'  => 'Aktif',
            ],
        ];

        foreach ($kendaraans as $k) {
            $nomor = $k['nomor_kendaraan'];
            unset($k['nomor_kendaraan']);

            Kendaraan::firstOrCreate(
                ['nomor_kendaraan' => $nomor],
                $k
            );
        }

        $this->call([
            BidangPernyataanSeeder::class,
            BbmReportDummySeeder::class,
            PortalDummyDataSeeder::class,
        ]);
    }
}
