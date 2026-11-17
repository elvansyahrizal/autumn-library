@auth
    @if (auth()->user()->isAnggota())
        @include('partials.shell.public')
    @else
        @include('partials.shell.staff')
    @endif
@else
    @include('partials.shell.public')
@endauth
