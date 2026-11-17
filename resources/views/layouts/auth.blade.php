<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#F6F1E6">
    <meta name="description" content="Masuk ke Ruang Baca, perpustakaan digital Politeknik Elektronika Negeri Surabaya.">
    <title>@hasSection('title')@yield('title') — Ruang Baca @else{{ isset($title) ? $title.' — Ruang Baca' : 'Ruang Baca — Perpustakaan Digital PENS' }} @endif</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body>
    <a href="#main" class="skip-link">Lewati ke konten</a>

    <main id="main" class="min-h-screen">
        @yield('content')
    </main>

    @livewireScripts
</body>
</html>
