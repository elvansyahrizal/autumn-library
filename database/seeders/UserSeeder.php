<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@pens.ac.id'],
            [
                'name' => 'Bambang Sutrisno',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'petugas1@pens.ac.id'],
            [
                'name' => 'Siti Rahmawati',
                'password' => Hash::make('password'),
                'role' => 'petugas',
            ]
        );

        User::updateOrCreate(
            ['email' => 'petugas2@pens.ac.id'],
            [
                'name' => 'Ahmad Fauzi',
                'password' => Hash::make('password'),
                'role' => 'petugas',
            ]
        );

        $studentUser = User::updateOrCreate(
            ['email' => 'mahasiswa@pens.ac.id'],
            [
                'name' => 'Budi Mahasiswa PENS',
                'password' => Hash::make('password'),
                'role' => 'anggota',
            ]
        );

        Member::updateOrCreate(
            ['nim' => '3123500001'],
            [
                'user_id' => $studentUser->id,
                'nama' => 'Budi Mahasiswa PENS',
                'email' => 'mahasiswa@pens.ac.id',
                'nomor_telepon' => '081234567890',
                'alamat' => 'Kampus PENS Sukolilo, Surabaya',
                'status' => 'aktif',
            ]
        );
    }
}
