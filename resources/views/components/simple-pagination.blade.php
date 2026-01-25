@if ($paginator->hasPages())
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $showPages = 5; // Number of page links to show
        $halfShow = floor($showPages / 2);
        
        $startPage = max(1, $currentPage - $halfShow);
        $endPage = min($lastPage, $currentPage + $halfShow);
        
        // Adjust if we're near the start or end
        if ($currentPage <= $halfShow) {
            $endPage = min($lastPage, $showPages);
        }
        if ($currentPage > $lastPage - $halfShow) {
            $startPage = max(1, $lastPage - $showPages + 1);
        }
    @endphp
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center flex-wrap">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link">Kembali</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">Kembali</a>
                </li>
            @endif

            {{-- First Page --}}
            @if ($startPage > 1)
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->url(1) }}">1</a>
                </li>
                @if ($startPage > 2)
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                @endif
            @endif

            {{-- Page Numbers --}}
            @foreach(range($startPage, $endPage) as $page)
                @if($page == $currentPage)
                    <li class="page-item active">
                        <span class="page-link">{{ $page }}</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->url($page) }}">{{ $page }}</a>
                    </li>
                @endif
            @endforeach

            {{-- Last Page --}}
            @if ($endPage < $lastPage)
                @if ($endPage < $lastPage - 1)
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                @endif
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->url($lastPage) }}">{{ $lastPage }}</a>
                </li>
            @endif

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Lanjut</a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link">Lanjut</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
