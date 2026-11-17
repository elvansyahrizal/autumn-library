@extends('layouts.app')

@section('title', 'Edit Anggota')

@section('content')
    <div class="space-y-6">
        <x-ui.page-header
            eyebrow="Master data / Anggota"
            title="Edit Anggota"
            description="Perbarui data keanggotaan. Perubahan email tidak otomatis mengubah akun login lama."
        >
            <x-slot:actions>
                <x-ui.button :href="route('members.index')" variant="secondary">
                    <x-ui.icon name="arrow-left" :size="15" />
                    Kembali
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        @include('members._form', ['member' => $member])
    </div>
@endsection
