@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman">
        <div class="flex items-center justify-between gap-3 sm:hidden">
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="Halaman sebelumnya"
                    class="inline-flex min-h-11 items-center justify-center rounded-md border border-brand/40 bg-transparent px-4 py-2 text-sm font-semibold text-brand/50 cursor-not-allowed">
                    Sebelumnya
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya"
                    class="inline-flex min-h-11 items-center justify-center rounded-md border border-brand bg-transparent px-4 py-2 text-sm font-semibold text-brand transition-colors hover:bg-brand-light hover:text-brand-dark focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand active:bg-brand active:text-white">
                    Sebelumnya
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya"
                    class="inline-flex min-h-11 items-center justify-center rounded-md border border-brand bg-transparent px-4 py-2 text-sm font-semibold text-brand transition-colors hover:bg-brand-light hover:text-brand-dark focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand active:bg-brand active:text-white">
                    Berikutnya
                </a>
            @else
                <span aria-disabled="true" aria-label="Halaman berikutnya"
                    class="inline-flex min-h-11 items-center justify-center rounded-md border border-brand/40 bg-transparent px-4 py-2 text-sm font-semibold text-brand/50 cursor-not-allowed">
                    Berikutnya
                </span>
            @endif
        </div>

        <div class="hidden sm:flex sm:items-center sm:justify-between">
            <p class="text-sm text-muted">
                Menampilkan
                @if ($paginator->firstItem())
                    <span class="font-semibold text-ink">{{ $paginator->firstItem() }}</span>
                    sampai
                    <span class="font-semibold text-ink">{{ $paginator->lastItem() }}</span>
                @else
                    {{ $paginator->count() }}
                @endif
                dari <span class="font-semibold text-ink">{{ $paginator->total() }}</span> hasil
            </p>

            <span class="inline-flex rtl:flex-row-reverse">
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" aria-label="Halaman sebelumnya"
                        class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-l-md border border-brand/40 bg-transparent px-2 py-2 text-sm font-medium text-brand/50 cursor-not-allowed">
                        <svg class="size-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya"
                        class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-l-md border border-brand bg-transparent px-2 py-2 text-sm font-medium text-brand transition-colors hover:bg-brand-light hover:text-brand-dark focus-visible:z-10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand active:bg-brand active:text-white">
                        <svg class="size-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </a>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span aria-disabled="true"
                            class="-ml-px inline-flex min-h-11 min-w-11 items-center justify-center border border-brand bg-transparent px-3 py-2 text-sm font-medium text-brand cursor-default">
                            {{ $element }}
                        </span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page"
                                    class="-ml-px inline-flex min-h-11 min-w-11 items-center justify-center border border-brand bg-brand px-3 py-2 text-sm font-semibold text-white cursor-default">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" aria-label="Ke halaman {{ $page }}"
                                    class="-ml-px inline-flex min-h-11 min-w-11 items-center justify-center border border-brand bg-transparent px-3 py-2 text-sm font-semibold text-brand transition-colors hover:bg-brand-light hover:text-brand-dark focus-visible:z-10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand active:bg-brand active:text-white">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya"
                        class="-ml-px inline-flex min-h-11 min-w-11 items-center justify-center rounded-r-md border border-brand bg-transparent px-2 py-2 text-sm font-medium text-brand transition-colors hover:bg-brand-light hover:text-brand-dark focus-visible:z-10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand active:bg-brand active:text-white">
                        <svg class="size-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </a>
                @else
                    <span aria-disabled="true" aria-label="Halaman berikutnya"
                        class="-ml-px inline-flex min-h-11 min-w-11 items-center justify-center rounded-r-md border border-brand/40 bg-transparent px-2 py-2 text-sm font-medium text-brand/50 cursor-not-allowed">
                        <svg class="size-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </span>
                @endif
            </span>
        </div>
    </nav>
@endif
