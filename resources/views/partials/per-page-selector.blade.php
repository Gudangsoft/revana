{{-- Per-page selector partial --}}
{{-- Usage: @include('partials.per-page-selector', ['paginator' => $variable, 'default' => 20]) --}}
@php
    $defaultPerPage = $default ?? 20;
    $perPageOptions = [10, 20, 50, 100];
@endphp

<div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
        <small class="text-muted">Tampilkan:</small>
        <select class="form-select form-select-sm" style="width: auto;" onchange="updatePerPage(this.value)">
            @foreach($perPageOptions as $pp)
                <option value="{{ $pp }}" {{ request('per_page', $defaultPerPage) == $pp ? 'selected' : '' }}>{{ $pp }}</option>
            @endforeach
        </select>
        <small class="text-muted">data per halaman</small>
    </div>
    <div>
        {{ $paginator->withQueryString()->links() }}
    </div>
</div>

<script>
if (typeof updatePerPage === 'undefined') {
    function updatePerPage(value) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', value);
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }
}
</script>
