{{--
    Partial: Format Sitasi Tambahan
    Variabel opsional: $referensiJurnal (untuk mode edit)
--}}
@php
    $styles = \App\Models\ReferensiJurnal::STYLE_LABELS;
    $existing = isset($referensiJurnal) ? ($referensiJurnal->format_sitasi ?? []) : [];
@endphp

<div class="mb-4">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <label class="form-label mb-0 fw-bold">
            <i class="bi bi-journal-richtext me-1" style="color:#6366f1;"></i>
            Format Sitasi
            <span class="badge bg-light text-muted border ms-1" style="font-size:.7rem;font-weight:500;">Opsional</span>
        </label>
        <button type="button" class="btn btn-sm btn-outline-secondary"
                id="toggleSitasi" onclick="toggleSitasiPanel()">
            <i class="bi bi-chevron-down" id="sitasiChevron"></i>
            <span id="sitasiToggleLabel">
                @if(count($existing) > 0)
                    {{ count($existing) }} format tersimpan — klik untuk edit
                @else
                    Tambah Format (APA, IEEE, Harvard…)
                @endif
            </span>
        </button>
    </div>

    <div id="sitasiPanel" style="{{ count($existing) > 0 ? '' : 'display:none;' }}">
        <div class="p-3 rounded-3 border" style="background:#f9fafb;">
            <p class="text-muted small mb-3">
                <i class="bi bi-info-circle me-1"></i>
                Isi format sitasi yang diinginkan. Kosongkan format yang tidak diperlukan.
                Format yang diisi akan tampil sebagai tab di halaman publik.
            </p>

            <div class="row g-3">
                @foreach($styles as $key => $label)
                @php $val = old('sitasi_' . strtolower($key), $existing[$key] ?? ''); @endphp
                <div class="col-12">
                    <div class="card border-0 shadow-sm" style="border-radius:10px; overflow:hidden;">
                        <div class="card-header py-2 px-3 d-flex align-items-center justify-content-between"
                             style="background: {{ match($key) {
                                'APA'       => '#ede9fe',
                                'IEEE'      => '#dbeafe',
                                'Harvard'   => '#dcfce7',
                                'Chicago'   => '#fef9c3',
                                'Vancouver' => '#ffedd5',
                                'MLA'       => '#fce7f3',
                                'ABNT'      => '#f0fdf4',
                                'ACS'       => '#f0f9ff',
                                'ACM'       => '#faf5ff',
                                default     => '#f3f4f6',
                             } }}; cursor:pointer;"
                             onclick="toggleSitasiField('field_{{ strtolower($key) }}', this)">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold" style="font-size:.82rem; min-width:72px;">{{ $label }}</span>
                                @if($val)
                                <span class="badge bg-success" style="font-size:.65rem;">Terisi</span>
                                @else
                                <span class="badge bg-light text-muted border" style="font-size:.65rem;">Kosong</span>
                                @endif
                            </div>
                            <i class="bi bi-chevron-{{ $val ? 'up' : 'down' }} text-muted field-chevron"></i>
                        </div>
                        <div id="field_{{ strtolower($key) }}" style="{{ $val ? '' : 'display:none;' }}" class="card-body p-2">
                            <textarea
                                name="sitasi_{{ strtolower($key) }}"
                                rows="3"
                                class="form-control form-control-sm font-monospace"
                                style="font-size:.8rem; line-height:1.6; border-radius:8px;"
                                placeholder="Tulis sitasi format {{ $label }} di sini..."
                            >{{ $val }}</textarea>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
function toggleSitasiPanel() {
    const panel   = document.getElementById('sitasiPanel');
    const chevron = document.getElementById('sitasiChevron');
    const label   = document.getElementById('sitasiToggleLabel');
    const open    = panel.style.display !== 'none';
    panel.style.display   = open ? 'none' : '';
    chevron.className     = open ? 'bi bi-chevron-down' : 'bi bi-chevron-up';
}

function toggleSitasiField(id, header) {
    const el  = document.getElementById(id);
    const ic  = header.querySelector('.field-chevron');
    const open = el.style.display !== 'none';
    el.style.display = open ? 'none' : '';
    ic.className = open ? 'bi bi-chevron-down text-muted field-chevron' : 'bi bi-chevron-up text-muted field-chevron';
}

/* Update badge saat user mengetik di textarea sitasi */
document.querySelectorAll('textarea[name^="sitasi_"]').forEach(ta => {
    const card   = ta.closest('.card');
    const badge  = card.querySelector('.badge');
    ta.addEventListener('input', () => {
        const filled = ta.value.trim().length > 0;
        badge.className  = filled ? 'badge bg-success' : 'badge bg-light text-muted border';
        badge.style.fontSize = '.65rem';
        badge.textContent    = filled ? 'Terisi' : 'Kosong';
    });
});
</script>
@endpush
@endonce
