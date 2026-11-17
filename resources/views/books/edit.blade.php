@extends('layouts.app')

@section('title', 'Edit Buku')

@section('content')
    <div class="space-y-6">
        <x-ui.page-header
            eyebrow="Master data / Buku"
            title="Edit Buku"
            description="Perbarui metadata atau stok buku. Perubahan langsung memengaruhi tampilan katalog publik."
        >
            <x-slot:actions>
                <x-ui.button :href="route('books.index')" variant="secondary">
                    <x-ui.icon name="arrow-left" :size="15" />
                    Kembali
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        @include('books._form', ['book' => $book])
    </div>
@endsection
