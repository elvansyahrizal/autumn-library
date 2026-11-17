<nav>
    <div class="brand">📚 Perpustakaan Digital Kampus</div>
    @auth
        <ul>
            @if (auth()->user()->isAnggota())
                <li><a href="{{ route('catalog.index') }}" class="{{ request()->routeIs('catalog.*') ? 'active' : '' }}">Katalog Buku</a></li>
                <li><a href="{{ route('my-loans.index') }}" class="{{ request()->routeIs('my-loans.*') ? 'active' : '' }}">Pinjaman Saya</a></li>
            @else
                <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a></li>
                <li><a href="{{ route('catalog.index') }}" class="{{ request()->routeIs('catalog.*') ? 'active' : '' }}">Katalog Mandiri</a></li>
                <li><a href="{{ route('books.index') }}" class="{{ request()->routeIs('books.*') ? 'active' : '' }}">Kelola Buku</a></li>
                @if (auth()->user()->isAdmin())
                    <li><a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'active' : '' }}">Kategori</a></li>
                @endif
                <li><a href="{{ route('members.index') }}" class="{{ request()->routeIs('members.*') ? 'active' : '' }}">Anggota</a></li>
                <li><a href="{{ route('loans.approval') }}" class="{{ request()->routeIs('loans.approval') ? 'active' : '' }}">Persetujuan & Sirkulasi</a></li>
                <li><a href="{{ route('loans.report') }}" class="{{ request()->routeIs('loans.report') ? 'active' : '' }}">Laporan</a></li>
            @endif
            <li><a href="{{ route('profile.show') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">Profil</a></li>
        </ul>
        <div class="navbar-user">
            <span>{{ auth()->user()->name }} ({{ ucfirst(auth()->user()->role) }})</span>
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn-logout">Logout</button>
            </form>
        </div>
    @endauth
    @guest
        <div>
            <a href="{{ route('catalog.index') }}" class="{{ request()->routeIs('catalog.*') ? 'active' : '' }}" style="color: #cbd5e1; margin-right: 16px; text-decoration: none;">Katalog</a>
            <a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'active' : '' }}">Login</a>
        </div>
    @endguest
</nav>
