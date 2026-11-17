@extends('layouts.app')

@section('title', 'Tambah Kategori')

@section('content')
    <div class="space-y-6">
        <x-ui.page-header
            eyebrow="Master data / Kategori"
            title="Tambah Kategori"
            description="Buat klasifikasi baru untuk merapikan koleksi di katalog."
        >
            <x-slot:actions>
                <x-ui.button :href="route('categories.index')" variant="secondary">
                    <x-ui.icon name="arrow-left" :size="15" />
                    Kembali
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        @include('categories._form', ['category' => null])
    </div>
@endsection
