{{-- Penulis Tambahan (Co-Authors) — include setelah blok Data Penulis utama --}}
{{-- Maksimal 6 co-author (total 7 penulis bersama penulis utama) --}}
{{-- Param opsional: $coAuthors (array dari model untuk edit form); default: old('co_authors', []) --}}
@php $coAuthors = $coAuthors ?? old('co_authors', []); @endphp
<div class="mt-3 mb-3">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <h6 class="text-muted mb-0">
            <i class="bi bi-people"></i> Penulis Tambahan
            <span class="text-muted fw-normal" style="font-size:.8rem;">(opsional, maks. 6)</span>
        </h6>
        <div class="d-flex align-items-center gap-2">
            <span id="co-author-counter" class="badge bg-light text-secondary border" style="font-size:.8rem;">
                0 / 6 penulis
            </span>
            <button type="button" id="btn-add-co-author" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-plus-circle"></i> + Tambah Penulis
            </button>
        </div>
    </div>

    <div id="co-authors-container">
        @foreach($coAuthors as $i => $co)
        @if(!empty($co['nama']))
        <div class="co-author-row border rounded bg-light p-3 mb-2">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <small class="fw-semibold text-primary">
                    <i class="bi bi-person-fill"></i> Penulis {{ $i + 2 }}
                    <span class="text-muted fw-normal" style="font-size:.75rem;">(dari maks. 7)</span>
                </small>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-co-author py-0 px-2">
                    <i class="bi bi-x-lg"></i> Hapus
                </button>
            </div>
            <div class="row g-2">
                <div class="col-12">
                    <label class="form-label form-label-sm mb-1">Nama Penulis <span class="text-danger">*</span></label>
                    <input type="text" name="co_authors[{{ $i }}][nama]" class="form-control form-control-sm"
                           value="{{ $co['nama'] ?? '' }}" placeholder="Nama lengkap penulis" required>
                </div>
                <div class="col-12">
                    <label class="form-label form-label-sm mb-1">Afiliasi <span class="text-muted fw-normal">(institusi/universitas)</span></label>
                    <input type="text" name="co_authors[{{ $i }}][afiliasi]" class="form-control form-control-sm"
                           value="{{ $co['afiliasi'] ?? '' }}" placeholder="Nama institusi / universitas penulis">
                </div>
                <div class="col-md-6">
                    <label class="form-label form-label-sm mb-1">No HP</label>
                    <input type="text" name="co_authors[{{ $i }}][no_hp]" class="form-control form-control-sm"
                           value="{{ $co['no_hp'] ?? '' }}" placeholder="08xx-xxxx-xxxx">
                </div>
                <div class="col-md-6">
                    <label class="form-label form-label-sm mb-1">Email</label>
                    <input type="email" name="co_authors[{{ $i }}][email]" class="form-control form-control-sm"
                           value="{{ $co['email'] ?? '' }}" placeholder="email@domain.com">
                </div>
            </div>
        </div>
        @endif
        @endforeach
    </div>
    <div id="co-author-max-notice" class="text-danger small mt-1" style="display:none;">
        <i class="bi bi-exclamation-circle"></i> Maksimal 7 penulis (1 utama + 6 tambahan) telah tercapai.
    </div>
</div>

<template id="co-author-tpl">
    <div class="co-author-row border rounded bg-light p-3 mb-2">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <small class="fw-semibold text-primary">
                <i class="bi bi-person-fill"></i> Penulis <span class="co-author-num"></span>
                <span class="text-muted fw-normal" style="font-size:.75rem;">(dari maks. 7)</span>
            </small>
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-co-author py-0 px-2">
                <i class="bi bi-x-lg"></i> Hapus
            </button>
        </div>
        <div class="row g-2">
            <div class="col-12">
                <label class="form-label form-label-sm mb-1">Nama Penulis <span class="text-danger">*</span></label>
                <input type="text" name="co_authors[__IDX__][nama]" class="form-control form-control-sm"
                       placeholder="Nama lengkap penulis" required>
            </div>
            <div class="col-12">
                <label class="form-label form-label-sm mb-1">Afiliasi <span class="text-muted fw-normal">(institusi/universitas)</span></label>
                <input type="text" name="co_authors[__IDX__][afiliasi]" class="form-control form-control-sm"
                       placeholder="Nama institusi / universitas penulis">
            </div>
            <div class="col-md-6">
                <label class="form-label form-label-sm mb-1">No HP</label>
                <input type="text" name="co_authors[__IDX__][no_hp]" class="form-control form-control-sm"
                       placeholder="08xx-xxxx-xxxx">
            </div>
            <div class="col-md-6">
                <label class="form-label form-label-sm mb-1">Email</label>
                <input type="email" name="co_authors[__IDX__][email]" class="form-control form-control-sm"
                       placeholder="email@domain.com">
            </div>
        </div>
    </div>
</template>

<script>
(function () {
    var MAX      = 6;
    var container = document.getElementById('co-authors-container');
    var tpl       = document.getElementById('co-author-tpl');
    var btnAdd    = document.getElementById('btn-add-co-author');
    var counter   = document.getElementById('co-author-counter');
    var notice    = document.getElementById('co-author-max-notice');

    function count() {
        return container.querySelectorAll('.co-author-row').length;
    }

    function updateUI() {
        var n = count();
        counter.textContent = n + ' / ' + MAX + ' penulis';
        counter.className   = n >= MAX
            ? 'badge bg-warning text-dark border'
            : 'badge bg-light text-secondary border';
        btnAdd.disabled     = n >= MAX;
        notice.style.display = n >= MAX ? 'block' : 'none';
    }

    function renumber() {
        container.querySelectorAll('.co-author-row').forEach(function (row, i) {
            var numEl = row.querySelector('.co-author-num');
            if (numEl) numEl.textContent = i + 2;
            row.querySelectorAll('[name]').forEach(function (inp) {
                inp.name = inp.name.replace(/co_authors\[[^\]]+\]/, 'co_authors[' + i + ']');
            });
        });
        updateUI();
    }

    btnAdd.addEventListener('click', function () {
        if (count() >= MAX) return;
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

    // init counter on page load (for old() repopulation)
    updateUI();
})();
</script>
