@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex flex-col items-center justify-between gap-4 border-t border-line pt-5 sm:flex-row">
        <p class="text-[11.5px] text-muted">
            Menampilkan
            <span class="num text-ink">{{ $paginator->firstItem() }}</span>
            &ndash;
            <span class="num text-ink">{{ $paginator->lastItem() }}</span>
            dari
            <span class="num text-ink">{{ $paginator->total() }}</span>
            hasil
        </p>

        <div class="flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="page-link" role="link" aria-disabled="true" aria-label="Halaman sebelumnya">
                    <x-ui.icon name="chevron-left" :size="15" />
                </span>
            @else
                <button
                    type="button"
                    wire:click="previousPage('{{ $paginator->getPageName() }}')"
                    wire:loading.attr="disabled"
                    class="page-link"
                    aria-label="Halaman sebelumnya"
                >
                    <x-ui.icon name="chevron-left" :size="15" />
                </button>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-1.5 text-[11px] text-muted">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="page-link" aria-current="page">{{ $page }}</span>
                        @else
                            <button
                                type="button"
                                wire:key="paginator-{{ $paginator->getPageName() }}-{{ $page }}"
                                wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                wire:loading.attr="disabled"
                                class="page-link"
                                aria-label="Ke halaman {{ $page }}"
                            >
                                {{ $page }}
                            </button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <button
                    type="button"
                    wire:click="nextPage('{{ $paginator->getPageName() }}')"
                    wire:loading.attr="disabled"
                    class="page-link"
                    aria-label="Halaman berikutnya"
                >
                    <x-ui.icon name="chevron-right" :size="15" />
                </button>
            @else
                <span class="page-link" role="link" aria-disabled="true" aria-label="Halaman berikutnya">
                    <x-ui.icon name="chevron-right" :size="15" />
                </span>
            @endif
        </div>
    </nav>
@endif
