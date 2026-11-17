@extends('layouts.app')

@section('title', 'Tambah Anggota')

@section('content')
    <div class="space-y-6">
        <x-ui.page-header
            eyebrow="Master data / Anggota"
            title="Tambah Anggota"
            description="Daftarkan mahasiswa sebagai anggota. Akun login dibuat otomatis dengan password awal NIM."
        >
            <x-slot:actions>
                <x-ui.button :href="route('members.index')" variant="secondary">
                    <x-ui.icon name="arrow-left" :size="15" />
                    Kembali
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        @include('members._form', ['member' => null])
    </div>
@endsection
