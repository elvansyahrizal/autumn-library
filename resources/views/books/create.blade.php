@extends('layouts.app')

@section('title', 'Tambah Buku')

@section('content')
    <div class="space-y-6">
        <x-ui.page-header
            eyebrow="Master data / Buku"
            title="Tambah Buku"
            description="Daftarkan judul baru ke koleksi. Stok awal menentukan jumlah eksemplar yang dapat diajukan."
        >
            <x-slot:actions>
                <x-ui.button :href="route('books.index')" variant="secondary">
                    <x-ui.icon name="arrow-left" :size="15" />
                    Kembali
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        @include('books._form', ['book' => null])
    </div>
@endsection
