@extends('layouts.app')

@section('title', 'Edit Kategori')

@section('content')
    <div class="space-y-6">
        <x-ui.page-header
            eyebrow="Master data / Kategori"
            title="Edit Kategori"
            description="Perbarui nama atau deskripsi kategori. Perubahan berlaku pada seluruh buku terkait."
        >
            <x-slot:actions>
                <x-ui.button :href="route('categories.index')" variant="secondary">
                    <x-ui.icon name="arrow-left" :size="15" />
                    Kembali
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        @include('categories._form', ['category' => $category])
    </div>
@endsection
