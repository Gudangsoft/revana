{{--
    Partial: Metadata Artikel — field penulis, judul, vol, no, halaman, doi
    + JS auto-generator format sitasi
    Variabel opsional: $referensiJurnal (mode edit)
--}}
@php
    $r = $referensiJurnal ?? null;
@endphp

<style>
.meta-section { border:none; border-radius:12px; background:#f8fafc; overflow:hidden; margin-bottom:16px; }
.meta-section-head {
    background: linear-gradient(90deg,#e0e7ff,#ede9fe);
    padding: 10px 16px; cursor:pointer;
    display:flex; align-items:center; justify-content:space-between;
    font-weight:700; font-size:.85rem; color:#3730a3;
    user-select:none; transition:background .15s;
}
.meta-section-head:hover { background:linear-gradient(90deg,#c7d2fe,#ddd6fe); }
.meta-body { padding:16px; border:1px solid #e0e7ff; border-top:none; border-radius:0 0 12px 12px; background:#fff; }
.meta-hint { font-size:.74rem; color:#9ca3af; margin-top:3px; }
.gen-badge {
    display:inline-flex; align-items:center; gap:4px;
    font-size:.68rem; font-weight:700; padding:2px 8px;
    background:#d1fae5; color:#065f46; border-radius:6px;
}
</style>

<div class="meta-section" id="metaSection">
    <div class="meta-section-head" onclick="toggleMeta()">
        <span>
            <i class="bi bi-card-list me-2" style="color:#6366f1;"></i>
            Metadata Artikel
            <span class="badge bg-light text-muted border ms-2" style="font-size:.68rem;font-weight:500;">Opsional — untuk auto-generate sitasi</span>
        </span>
        <i class="bi bi-chevron-{{ $r && ($r->penulis || $r->judul_artikel) ? 'up' : 'down' }}" id="metaChevron"></i>
    </div>
    <div id="metaBody" class="meta-body" style="{{ $r && ($r->penulis || $r->judul_artikel) ? '' : 'display:none;' }}">
        <div class="row g-3">
            {{-- Penulis --}}
            <div class="col-12">
                <label class="form-label">Penulis
                    <small class="text-muted fw-normal">(format: Lastname, F.M., & Lastname2, F.M.)</small>
                </label>
                <input type="text" name="penulis" id="meta_penulis" class="form-control"
                       placeholder="Rahmadani, P. A., Tohar, I., & Hakim, R."
                       value="{{ old('penulis', $r->penulis ?? '') }}">
                <div class="meta-hint">Pisahkan penulis dengan koma, penulis terakhir pakai &amp;</div>
            </div>
            {{-- Judul Artikel --}}
            <div class="col-12">
                <label class="form-label">Judul Artikel</label>
                <input type="text" name="judul_artikel" id="meta_judul" class="form-control"
                       placeholder="Identifikasi Permasalahan Arsitektur Perpustakaan Umum"
                       value="{{ old('judul_artikel', $r->judul_artikel ?? '') }}">
            </div>
            {{-- Volume, Nomor, Halaman, DOI --}}
            <div class="col-md-3">
                <label class="form-label">Volume</label>
                <input type="text" name="volume" id="meta_vol" class="form-control"
                       placeholder="4"
                       value="{{ old('volume', $r->volume ?? '') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Nomor/Issue</label>
                <input type="text" name="nomor" id="meta_no" class="form-control"
                       placeholder="2"
                       value="{{ old('nomor', $r->nomor ?? '') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Halaman</label>
                <input type="text" name="halaman" id="meta_hal" class="form-control"
                       placeholder="01–10"
                       value="{{ old('halaman', $r->halaman ?? '') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">DOI</label>
                <input type="text" name="doi" id="meta_doi" class="form-control"
                       placeholder="10.61132/xxx"
                       value="{{ old('doi', $r->doi ?? '') }}">
            </div>
        </div>

        {{-- Generate button --}}
        <div class="mt-3 pt-3 border-top d-flex align-items-center gap-3 flex-wrap">
            <button type="button" class="btn btn-primary btn-sm" onclick="generateAllFormats()">
                <i class="bi bi-magic me-1"></i>Generate Semua Format Sitasi
            </button>
            <span class="text-muted small">Hasil generate akan mengisi field format di bawah. Anda bisa edit hasilnya.</span>
            <span class="gen-badge d-none" id="genBadge"><i class="bi bi-check2-circle"></i> Berhasil digenerate</span>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
/* ── Toggle metadata panel ── */
function toggleMeta() {
    const b = document.getElementById('metaBody');
    const c = document.getElementById('metaChevron');
    const open = b.style.display !== 'none';
    b.style.display = open ? 'none' : '';
    c.className = open ? 'bi bi-chevron-down' : 'bi bi-chevron-up';
}

/* ══ CITATION GENERATOR ══ */

/**
 * Parse "Lastname, F. M., & Lastname2, F.M." → array of {last, initials}
 * Handles: "Rahmadani, P. A., Tohar, I., & Hakim, R."
 */
function parseAuthors(raw) {
    if (!raw) return [];
    // Remove & then split by ", " patterns
    const cleaned = raw.replace(/\s*&\s*/g, ',');
    // Split by pattern: ", Uppercase." boundary — simple approach: split on "; " or ", " before uppercase+dot
    // Better: split on "., " or just ", " followed by a capitalized Lastname
    const authors = [];
    const parts = cleaned.split(/,\s*(?=[A-Z][a-z])/); // split before Lastname
    // parts might be: ["Rahmadani", "P. A.", "Tohar", "I.", "Hakim", "R."]
    // Group in pairs
    for (let i = 0; i < parts.length; i += 2) {
        const last     = (parts[i]     || '').trim().replace(/\.$/, '');
        const initials = (parts[i + 1] || '').trim().replace(/\.$/, '');
        if (last) authors.push({ last, initials });
    }
    return authors;
}

/**
 * Format author list for each style
 */
function fmtAuthorsAPA(authors) {
    if (!authors.length) return '';
    return authors.map((a, i) => {
        const ini = a.initials ? `, ${a.initials}.` : '';
        return `${a.last}${ini}`;
    }).join(', ').replace(/, ([^,]+)$/, ', & $1');
}

function fmtAuthorsIEEE(authors) {
    if (!authors.length) return '';
    const list = authors.map(a => {
        const ini = a.initials ? `${a.initials}. ` : '';
        return `${ini}${a.last}`;
    });
    if (list.length === 1) return list[0];
    if (list.length === 2) return list.join(' and ');
    return list.slice(0, -1).join(', ') + ', and ' + list[list.length - 1];
}

function fmtAuthorsHarvard(authors) {
    if (!authors.length) return '';
    return authors.map(a => {
        const ini = a.initials ? `${a.initials.replace(/\s/g,'')}` : '';
        return `${a.last}, ${ini}.`;
    }).join(' and ');
}

function fmtAuthorsVancouver(authors) {
    if (!authors.length) return '';
    return authors.map(a => {
        const ini = a.initials ? a.initials.replace(/\.\s*/g, '') : '';
        return `${a.last} ${ini}`;
    }).join(', ');
}

function fmtAuthorsMLAFirst(authors) {
    if (!authors.length) return '';
    const first = `${authors[0].last}, ${authors[0].initials || ''}`.trim().replace(/,\s*$/,'');
    if (authors.length === 1) return first;
    if (authors.length === 2) return `${first}, and ${authors[1].initials || ''} ${authors[1].last}`.trim();
    return `${first}, et al.`;
}

function fmtAuthorsChicago(authors) {
    if (!authors.length) return '';
    const list = authors.map((a, i) => {
        if (i === 0) return `${a.last}, ${a.initials || ''}`.trim().replace(/,\s*$/,'');
        return `${a.initials || ''} ${a.last}`.trim();
    });
    if (list.length <= 2) return list.join(', and ');
    return list.slice(0,-1).join(', ') + ', and ' + list[list.length-1];
}

/* ── Main generator ── */
function generateAllFormats() {
    const penulis = document.getElementById('meta_penulis').value.trim();
    const judul   = document.getElementById('meta_judul').value.trim();
    const tahun   = document.getElementById('tahun').value.trim();
    const jurnal  = document.getElementById('nama_jurnal').value.trim();
    const vol     = document.getElementById('meta_vol').value.trim();
    const no      = document.getElementById('meta_no').value.trim();
    const hal     = document.getElementById('meta_hal').value.trim();
    const doi     = document.getElementById('meta_doi').value.trim();

    if (!penulis && !judul) {
        alert('Isi minimal Penulis dan Judul Artikel untuk generate sitasi.');
        return;
    }

    const authors  = parseAuthors(penulis);
    const doiStr   = doi ? ` https://doi.org/${doi.replace(/^https?:\/\/doi\.org\//,'')}` : '';
    const doiIEEE  = doi ? `, doi: ${doi.replace(/^https?:\/\/doi\.org\//,'')}` : '';
    const volNo    = vol && no ? `${vol}(${no})` : (vol || no || '');
    const volNoIEEE= vol ? `vol. ${vol}` + (no ? `, no. ${no}` : '') : '';
    const ppHal    = hal ? `pp. ${hal}` : '';
    const halAPA   = hal ? `, ${hal}` : '';

    const formats = {};

    // ── APA ──
    formats['APA'] = `${fmtAuthorsAPA(authors)} (${tahun}). ${judul}. ${jurnal}${volNo ? `, ${volNo}` : ''}${halAPA}.${doiStr}`;

    // ── IEEE ──
    formats['IEEE'] = `${fmtAuthorsIEEE(authors)}, "${judul}," ${jurnal}${volNoIEEE ? `, ${volNoIEEE}` : ''}${ppHal ? `, ${ppHal}` : ''}, ${tahun}${doiIEEE}.`;

    // ── Harvard ──
    formats['Harvard'] = `${fmtAuthorsHarvard(authors)} (${tahun}) '${judul}', ${jurnal}${volNo ? `, ${volNo}` : ''}${halAPA}.`;

    // ── Chicago ──
    formats['Chicago'] = `${fmtAuthorsChicago(authors)}. "${judul}." ${jurnal}${vol ? ` ${vol}` : ''}${no ? `, no. ${no}` : ''} (${tahun})${halAPA ? `: ${hal}` : ''}.${doiStr}`;

    // ── Vancouver ──
    formats['Vancouver'] = `${fmtAuthorsVancouver(authors)}. ${judul}. ${jurnal}. ${tahun}${volNo ? `;${volNo}` : ''}${hal ? `:${hal}` : ''}.${doiStr}`;

    // ── MLA ──
    formats['MLA'] = `${fmtAuthorsMLAFirst(authors)}. "${judul}." ${jurnal}${vol ? `, vol. ${vol}` : ''}${no ? `, no. ${no}` : ''}, ${tahun}${hal ? `, pp. ${hal}` : ''}.`;

    // ── ABNT ──
    const abntAuthors = authors.map(a => `${a.last.toUpperCase()}, ${(a.initials||'').replace(/\s/g,'')}.`).join('; ');
    formats['ABNT'] = `${abntAuthors} ${judul}. ${jurnal}${volNo ? `, v. ${vol}, n. ${no}` : ''}${hal ? `, p. ${hal}` : ''}, ${tahun}.${doiStr}`;

    // ── ACS ──
    const acsAuthors = authors.map(a => `${a.last}, ${(a.initials||'').replace(/\./g,'').trim()}.`).join('; ');
    formats['ACS'] = `${acsAuthors} ${judul}. ${jurnal} ${tahun}${vol ? `, ${vol}` : ''}${no ? ` (${no})` : ''}${hal ? `, ${hal}` : ''}.${doiStr}`;

    // ── ACM ──
    const acmAuthors = authors.map(a => `${a.initials||''} ${a.last}`.trim()).join(', ');
    formats['ACM'] = `${acmAuthors}. ${tahun}. ${judul}. ${jurnal}${vol ? ` ${vol}` : ''}${no ? `, ${no}` : ''} (${tahun})${hal ? `, ${hal}` : ''}.${doiStr}`;

    // ── Isi ke textarea masing-masing style ──
    Object.entries(formats).forEach(([key, text]) => {
        const ta = document.querySelector(`textarea[name="sitasi_${key.toLowerCase()}"]`);
        if (ta) {
            ta.value = text.replace(/\s{2,}/g,' ').trim();
            // Update badge
            const badge = ta.closest('.card')?.querySelector('.badge');
            if (badge) {
                badge.className = 'badge bg-success';
                badge.style.fontSize = '.65rem';
                badge.textContent = 'Terisi';
            }
            // Open the field
            const fieldId = `field_${key.toLowerCase()}`;
            const el = document.getElementById(fieldId);
            if (el) {
                el.style.display = '';
                const chv = document.querySelector(`[onclick*="${fieldId}"] .field-chevron`);
                if (chv) chv.className = 'bi bi-chevron-up text-muted field-chevron';
            }
        }
    });

    // Pastikan panel sitasi terbuka
    document.getElementById('sitasiPanel').style.display = '';
    document.getElementById('sitasiChevron').className = 'bi bi-chevron-up';

    // Badge sukses
    const badge = document.getElementById('genBadge');
    badge.classList.remove('d-none');
    setTimeout(() => badge.classList.add('d-none'), 3000);
}

/* ── Auto-trigger saat field metadata berubah (opsional: generate on blur) ── */
['meta_penulis','meta_judul','meta_vol','meta_no','meta_hal','meta_doi'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('blur', () => {
        const penulis = document.getElementById('meta_penulis')?.value.trim();
        const judul   = document.getElementById('meta_judul')?.value.trim();
        if (penulis && judul) generateAllFormats();
    });
});
</script>
@endpush
@endonce
