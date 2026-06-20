{{-- Penulis Tambahan (Co-Authors) — include setelah blok Data Penulis utama --}}
<div class="mt-3 mb-3">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <h6 class="text-muted mb-0">
            <i class="bi bi-people"></i> Penulis Tambahan
            <span class="text-muted fw-normal" style="font-size:.8rem;">(opsional)</span>
        </h6>
        <button type="button" id="btn-add-co-author" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-plus-circle"></i> + Tambah Penulis
        </button>
    </div>

    <div id="co-authors-container">
        @foreach(old('co_authors', []) as $i => $co)
        @if(!empty($co['nama']))
        <div class="co-author-row border rounded bg-light p-3 mb-2">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <small class="fw-semibold text-primary">
                    <i class="bi bi-person-fill"></i> Penulis {{ $i + 2 }}
                </small>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-co-author py-0 px-2">
                    <i class="bi bi-x-lg"></i> Hapus
                </button>
            </div>
            <div class="row g-2">
                <div class="col-md-5">
                    <label class="form-label form-label-sm mb-1">Nama <span class="text-danger">*</span></label>
                    <input type="text" name="co_authors[{{ $i }}][nama]" class="form-control form-control-sm"
                           value="{{ $co['nama'] ?? '' }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label form-label-sm mb-1">No HP</label>
                    <input type="text" name="co_authors[{{ $i }}][no_hp]" class="form-control form-control-sm"
                           value="{{ $co['no_hp'] ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label form-label-sm mb-1">Email</label>
                    <input type="email" name="co_authors[{{ $i }}][email]" class="form-control form-control-sm"
                           value="{{ $co['email'] ?? '' }}" placeholder="email@domain.com">
                </div>
                <div class="col-12">
                    <label class="form-label form-label-sm mb-1">Afiliasi <span class="text-muted fw-normal">(institusi/universitas)</span></label>
                    <input type="text" name="co_authors[{{ $i }}][afiliasi]" class="form-control form-control-sm"
                           value="{{ $co['afiliasi'] ?? '' }}" placeholder="Nama institusi / universitas">
                </div>
            </div>
        </div>
        @endif
        @endforeach
    </div>
</div>

<template id="co-author-tpl">
    <div class="co-author-row border rounded bg-light p-3 mb-2">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <small class="fw-semibold text-primary">
                <i class="bi bi-person-fill"></i> Penulis <span class="co-author-num"></span>
            </small>
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-co-author py-0 px-2">
                <i class="bi bi-x-lg"></i> Hapus
            </button>
        </div>
        <div class="row g-2">
            <div class="col-md-5">
                <label class="form-label form-label-sm mb-1">Nama <span class="text-danger">*</span></label>
                <input type="text" name="co_authors[__IDX__][nama]" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-3">
                <label class="form-label form-label-sm mb-1">No HP</label>
                <input type="text" name="co_authors[__IDX__][no_hp]" class="form-control form-control-sm">
            </div>
            <div class="col-md-4">
                <label class="form-label form-label-sm mb-1">Email</label>
                <input type="email" name="co_authors[__IDX__][email]" class="form-control form-control-sm" placeholder="email@domain.com">
            </div>
            <div class="col-12">
                <label class="form-label form-label-sm mb-1">Afiliasi <span class="text-muted fw-normal">(institusi/universitas)</span></label>
                <input type="text" name="co_authors[__IDX__][afiliasi]" class="form-control form-control-sm" placeholder="Nama institusi / universitas">
            </div>
        </div>
    </div>
</template>

<script>
(function () {
    var container = document.getElementById('co-authors-container');
    var tpl       = document.getElementById('co-author-tpl');
    var btnAdd    = document.getElementById('btn-add-co-author');

    function renumber() {
        container.querySelectorAll('.co-author-row').forEach(function (row, i) {
            var numEl = row.querySelector('.co-author-num');
            if (numEl) numEl.textContent = i + 2;
            row.querySelectorAll('[name]').forEach(function (inp) {
                inp.name = inp.name.replace(/co_authors\[[^\]]+\]/, 'co_authors[' + i + ']');
            });
        });
    }

    btnAdd.addEventListener('click', function () {
        var html = tpl.innerHTML.replace(/__IDX__/g, 'new');
        container.insertAdjacentHTML('beforeend', html);
        renumber();
        var firstInput = container.lastElementChild.querySelector('input');
        if (firstInput) firstInput.focus();
    });

    container.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-remove-co-author');
        if (btn) {
            btn.closest('.co-author-row').remove();
            renumber();
        }
    });
})();
</script>
