<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        // UserSeeder sudah membuat satu anggota demo; lengkapi sampai 16 anggota.
        if (Member::count() <= 1) {
            Member::factory()->count(15)->create();
        }

        // Setiap anggota wajib punya akun login. Password awal = NIM.
        Member::query()->with('user')->orderBy('id')->each(function (Member $member) {
            if ($member->user) {
                return;
            }

            $user = $this->resolveUserFor($member);

            $member->update(['user_id' => $user->id]);
        });
    }

    private function resolveUserFor(Member $member): User
    {
        $attributes = [
            'name' => $member->nama,
            'password' => Hash::make($member->nim),
            'role' => 'anggota',
        ];

        // Pakai email anggota bila belum dipakai akun non-anggota.
        if ($member->email) {
            $existing = User::where('email', $member->email)->first();

            if (! $existing) {
                return User::create(['email' => $member->email] + $attributes);
            }

            if ($existing->isAnggota()) {
                return $existing;
            }
        }

        $fallbackEmail = $member->nim.'@mahasiswa.pens.ac.id';
        $existing = User::where('email', $fallbackEmail)->first();

        if ($existing) {
            return $existing;
        }

        return User::create(['email' => $fallbackEmail] + $attributes);
    }
}
