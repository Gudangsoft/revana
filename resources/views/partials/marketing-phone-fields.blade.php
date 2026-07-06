{{-- Field Telepon Marketing + nomor tambahan (opsional, bisa lebih dari 1) --}}
{{-- Param opsional: $phones (array nomor tambahan untuk edit form); default: old('additional_phones', []) --}}
@php $phones = $phones ?? old('additional_phones', []); @endphp

<div class="mb-3">
    <label class="form-label">Telepon</label>
    <div class="input-group">
        <input type="text" class="form-control @error('phone') is-invalid @enderror"
               name="phone" value="{{ old('phone', $marketing->phone ?? '') }}"
               placeholder="081234567890">
        <button type="button" class="btn btn-outline-secondary" id="btn-add-phone" title="Tambah nomor lain">
            <i class="bi bi-plus-lg"></i>
        </button>
        @error('phone')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div id="additional-phones-container" class="mt-2">
        @foreach($phones as $p)
        @if(!empty($p))
        <div class="input-group mb-2 additional-phone-row">
            <input type="text" class="form-control" name="additional_phones[]" value="{{ $p }}" placeholder="Nomor tambahan">
            <button type="button" class="btn btn-outline-danger btn-remove-phone"><i class="bi bi-x-lg"></i></button>
        </div>
        @endif
        @endforeach
    </div>
</div>

<template id="phone-tpl">
    <div class="input-group mb-2 additional-phone-row">
        <input type="text" class="form-control" name="additional_phones[]" placeholder="Nomor tambahan">
        <button type="button" class="btn btn-outline-danger btn-remove-phone"><i class="bi bi-x-lg"></i></button>
    </div>
</template>

<script>
(function () {
    var container = document.getElementById('additional-phones-container');
    var tpl       = document.getElementById('phone-tpl');
    var btnAdd    = document.getElementById('btn-add-phone');

    btnAdd.addEventListener('click', function () {
        container.insertAdjacentHTML('beforeend', tpl.innerHTML);
        var last = container.lastElementChild.querySelector('input');
        if (last) last.focus();
    });

    container.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-remove-phone');
        if (btn) btn.closest('.additional-phone-row').remove();
    });
})();
</script>
