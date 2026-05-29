{{--
    Partial: Preview Format Sitasi Otomatis (read-only, generated dari metadata)
    Format digenerate server-side oleh CitationGenerator saat simpan/update.
--}}
@php
    $existing = isset($referensiJurnal) ? ($referensiJurnal->format_sitasi ?? []) : [];
    $styles   = \App\Models\ReferensiJurnal::STYLE_LABELS;
    $colors   = [
        'APA'       => ['bg'=>'#ede9fe','text'=>'#5b21b6','border'=>'#c4b5fd'],
        'IEEE'      => ['bg'=>'#dbeafe','text'=>'#1e40af','border'=>'#93c5fd'],
        'Harvard'   => ['bg'=>'#dcfce7','text'=>'#166534','border'=>'#86efac'],
        'Chicago'   => ['bg'=>'#fef9c3','text'=>'#854d0e','border'=>'#fde047'],
        'Vancouver' => ['bg'=>'#ffedd5','text'=>'#9a3412','border'=>'#fdba74'],
        'MLA'       => ['bg'=>'#fce7f3','text'=>'#9d174d','border'=>'#f9a8d4'],
        'ABNT'      => ['bg'=>'#f0fdf4','text'=>'#14532d','border'=>'#86efac'],
        'ACS'       => ['bg'=>'#f0f9ff','text'=>'#0c4a6e','border'=>'#7dd3fc'],
        'ACM'       => ['bg'=>'#faf5ff','text'=>'#581c87','border'=>'#d8b4fe'],
    ];
@endphp

<div class="mb-4" id="sitasiPreviewWrap">
    <div class="d-flex align-items-center gap-2 mb-2">
        <i class="bi bi-journal-richtext" style="color:#6366f1;font-size:1rem;"></i>
        <span class="fw-bold" style="font-size:.87rem;">Format Sitasi Otomatis</span>
        <span class="badge rounded-pill" id="sitasiCountBadge"
              style="background:#ede9fe;color:#5b21b6;font-size:.68rem;font-weight:700;">
            {{ count($existing) ? count($existing) . ' format' : 'Belum digenerate' }}
        </span>
        <span class="text-muted ms-1" style="font-size:.72rem;">
            — otomatis dibuat saat <strong>Simpan</strong> berdasarkan metadata
        </span>
    </div>

    {{-- Tab selector untuk preview --}}
    <div id="sitasiTabBar" class="d-flex flex-wrap gap-1 mb-2">
        @foreach($styles as $key => $label)
        @php $c = $colors[$key] ?? ['bg'=>'#f3f4f6','text'=>'#374151','border'=>'#d1d5db']; @endphp
        <button type="button"
                class="sitasi-tab-btn {{ $loop->first ? 'active' : '' }}"
                data-style="{{ $key }}"
                style="padding:3px 12px; font-size:.74rem; font-weight:700; border-radius:8px;
                       border:1.5px solid {{ $c['border'] }};
                       background:{{ $loop->first ? $c['bg'] : '#fff' }};
                       color:{{ $c['text'] }}; cursor:pointer; transition:all .13s;"
                onclick="switchSitasiTab('{{ $key }}', this)">
            {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- Preview box per style --}}
    @foreach($styles as $key => $label)
    @php
        $c   = $colors[$key] ?? ['bg'=>'#f3f4f6','text'=>'#374151','border'=>'#d1d5db'];
        $val = $existing[$key] ?? '';
    @endphp
    <div class="sitasi-preview-pane {{ $loop->first ? '' : 'd-none' }}" id="preview_{{ $key }}">
        <div class="p-3 rounded-3 position-relative"
             style="background:{{ $c['bg'] }}; border:1.5px solid {{ $c['border'] }}; min-height:64px;">
            <div class="d-flex justify-content-between align-items-start gap-2">
                <p class="sitasi-preview-text mb-0"
                   id="previewText_{{ $key }}"
                   style="font-size:.86rem; line-height:1.75; color:{{ $c['text'] }};
                          font-family:'Segoe UI',sans-serif; flex:1;">
                    @if($val)
                        {{ $val }}
                    @else
                        <span class="text-muted fst-italic" style="font-size:.8rem;">
                            Isi metadata dan klik <strong>Generate Preview</strong> atau langsung <strong>Simpan</strong>
                        </span>
                    @endif
                </p>
                @if($val)
                <button type="button" class="copy-preview-btn flex-shrink-0"
                        onclick="copyPreview('{{ $key }}')"
                        style="padding:2px 10px;font-size:.71rem;font-weight:600;
                               border-radius:7px;border:1px solid {{ $c['border'] }};
                               background:#fff;color:{{ $c['text'] }};cursor:pointer;">
                    <i class="bi bi-clipboard me-1"></i>Salin
                </button>
                @endif
            </div>
        </div>
    </div>
    @endforeach

    {{-- Generate preview button (JS-side, tidak simpan ke DB) --}}
    <div class="mt-2 d-flex gap-2 align-items-center">
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="liveGeneratePreview()"
                style="border-radius:9px;font-size:.8rem;">
            <i class="bi bi-lightning-charge-fill me-1"></i>Generate Preview
        </button>
        <span class="text-muted" style="font-size:.74rem;">
            Preview langsung dari metadata di atas — format final disimpan saat klik Simpan/Update
        </span>
    </div>
</div>

@once
@push('scripts')
<script>
/* ── Tab switch ── */
function switchSitasiTab(key, btn) {
    document.querySelectorAll('.sitasi-preview-pane').forEach(p => p.classList.add('d-none'));
    document.getElementById('preview_' + key)?.classList.remove('d-none');
    document.querySelectorAll('.sitasi-tab-btn').forEach(b => {
        b.style.fontWeight = '700';
        b.style.background = '#fff';
    });
    btn.style.background = btn.style.borderColor.replace(')', ', 0.25)').replace('rgb', 'rgba') || '#ede9fe';
    btn.style.background = btn.style.borderColor;
}

/* ── Live preview dari JS (mirror CitationGenerator PHP) ── */
function liveGeneratePreview() {
    // Ambil nilai dari form
    const penulis = (document.getElementById('meta_penulis')?.value || '').trim();
    const judul   = (document.getElementById('meta_judul')?.value   || '').trim();
    const jurnal  = (document.getElementById('nama_jurnal')?.value   || '').trim();
    const tahun   = (document.getElementById('tahun')?.value         || '').trim();
    const vol     = (document.getElementById('meta_vol')?.value      || '').trim();
    const no      = (document.getElementById('meta_no')?.value       || '').trim();
    const hal     = (document.getElementById('meta_hal')?.value      || '').trim();
    const doi     = (document.getElementById('meta_doi')?.value      || '').trim();

    if (!penulis && !judul) {
        alert('Isi minimal Penulis dan Judul Artikel di bagian Metadata Artikel untuk generate preview.');
        return;
    }

    // Gunakan generator yang sudah ada di _metadata_artikel.blade.php
    // (fungsi sudah terdefinisi global di halaman)
    if (typeof generateAllFormats === 'function') {
        // Tangkap hasil dari generateAllFormats — ambil dari textarea tersembunyi
        // Karena generateAllFormats sudah ada, kita duplikasi logikanya di sini
    }

    const authors  = typeof parseAuthors === 'function' ? parseAuthors(penulis) : [];
    const doiClean = doi.replace(/^https?:\/\/doi\.org\//,'');
    const doiUrl   = doi ? ' https://doi.org/' + doiClean : '';
    const doiIEEE  = doi ? ', doi: ' + doiClean : '';
    const vn       = vol && no ? `${vol}(${no})` : (vol || no || '');
    const hal_     = hal ? `, ${hal}` : '';
    const pp_      = hal ? `pp. ${hal}` : '';
    const vnIEEE   = (vol ? `vol. ${vol}` : '') + (no ? `, no. ${no}` : '');

    const a_apa  = typeof fmtAuthorsAPA      === 'function' ? fmtAuthorsAPA(authors)      : penulis;
    const a_ieee = typeof fmtAuthorsIEEE     === 'function' ? fmtAuthorsIEEE(authors)     : penulis;
    const a_harv = typeof fmtAuthorsHarvard  === 'function' ? fmtAuthorsHarvard(authors)  : penulis;
    const a_chic = typeof fmtAuthorsChicago  === 'function' ? fmtAuthorsChicago(authors)  : penulis;
    const a_vanc = typeof fmtAuthorsVancouver=== 'function' ? fmtAuthorsVancouver(authors): penulis;
    const a_mla  = typeof fmtAuthorsMLAFirst === 'function' ? fmtAuthorsMLAFirst(authors) : penulis;

    const formats = {
        APA:       `${a_apa} (${tahun}). ${judul}. ${jurnal}${vn?', '+vn:''}${hal_}.${doiUrl}`,
        IEEE:      `${a_ieee}, "${judul}," ${jurnal}${vnIEEE?', '+vnIEEE:''}${pp_?', '+pp_:''}${tahun?', '+tahun:''}${doiIEEE}.`,
        Harvard:   `${a_harv} (${tahun}) '${judul}', ${jurnal}${vn?', '+vn:''}${hal?', pp.'+hal:''}.${doiUrl}`,
        Chicago:   `${a_chic}. "${judul}." ${jurnal}${vol?' '+vol:''}${no?', no. '+no:''} (${tahun})${hal?': '+hal:''}.${doiUrl}`,
        Vancouver: `${a_vanc}. ${judul}. ${jurnal}. ${tahun}${vn?';'+vn:''}${hal?':'+hal:''}.${doiUrl}`,
        MLA:       `${a_mla}. "${judul}." ${jurnal}${vol?', vol. '+vol:''}${no?', no. '+no:''}, ${tahun}${hal?', pp. '+hal:''}.`,
        ABNT:      `${penulis.toUpperCase()}. ${judul}. ${jurnal}${vol?', v. '+vol:''}${no?', n. '+no:''}${hal?', p. '+hal:''}, ${tahun}.${doiUrl}`,
    };

    let count = 0;
    Object.entries(formats).forEach(([key, text]) => {
        const el = document.getElementById('previewText_' + key);
        if (el) {
            const clean = text.replace(/\s{2,}/g,' ').trim();
            el.style.fontStyle = 'normal';
            el.textContent = clean;
            count++;
        }
    });

    document.getElementById('sitasiCountBadge').textContent = count + ' format';

    // Buka panel metadata jika belum terbuka
    const metaBody = document.getElementById('metaBody');
    if (metaBody && metaBody.style.display === 'none') toggleMeta?.();
}

/* ── Copy preview ── */
function copyPreview(key) {
    const text = document.getElementById('previewText_' + key)?.textContent?.trim();
    if (!text) return;
    navigator.clipboard.writeText(text).then(() => {
        const btns = document.querySelectorAll('.copy-preview-btn');
        btns.forEach(b => { if (b.getAttribute('onclick')?.includes(key)) {
            const orig = b.innerHTML;
            b.innerHTML = '<i class="bi bi-check2-circle me-1"></i>Tersalin!';
            setTimeout(() => b.innerHTML = orig, 2000);
        }});
    });
}
</script>
@endpush
@endonce
