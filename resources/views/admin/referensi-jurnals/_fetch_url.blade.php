{{-- Partial: Fetch metadata dari URL artikel --}}
<div class="mb-4 p-3 rounded-3" style="background:linear-gradient(135deg,#f0f4ff,#f5f3ff); border:1px solid #c7d2fe;">
    <div class="d-flex align-items-center gap-2 mb-2">
        <i class="bi bi-link-45deg fs-5" style="color:#6366f1;"></i>
        <span class="fw-bold" style="font-size:.88rem; color:#3730a3;">Ambil Metadata dari URL Artikel</span>
        <span class="badge bg-light text-muted border" style="font-size:.68rem;">Opsional — otomatis isi semua field</span>
    </div>
    <p class="text-muted mb-2" style="font-size:.78rem;">
        Tempel URL halaman artikel jurnal (OJS, SINTA, DOAJ, dll) — sistem akan membaca metadata dan mengisi form otomatis.
    </p>
    <div class="d-flex gap-2">
        <input type="url" id="fetchUrl" class="form-control form-control-sm"
               placeholder="https://diajeng.lldikti6.id/articles/14750"
               style="border-radius:9px; font-size:.84rem;">
        <button type="button" class="btn btn-primary btn-sm px-3 flex-shrink-0" id="fetchBtn" onclick="fetchMetadata()">
            <i class="bi bi-cloud-download-fill me-1"></i>Ambil Data
        </button>
    </div>
    <div id="fetchStatus" class="mt-2 d-none"></div>
</div>

@once
@push('scripts')
<script>
async function fetchMetadata() {
    const url = document.getElementById('fetchUrl').value.trim();
    if (!url) { alert('Masukkan URL artikel terlebih dahulu.'); return; }

    const btn    = document.getElementById('fetchBtn');
    const status = document.getElementById('fetchStatus');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Mengambil...';
    status.className = 'mt-2';
    status.innerHTML = '';

    try {
        const resp = await fetch('{{ route("admin.referensi-jurnals.fetch-url") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                              || '{{ csrf_token() }}'
            },
            body: JSON.stringify({ url })
        });

        const data = await resp.json();

        if (!resp.ok || data.error) {
            status.className = 'mt-2 alert alert-danger py-2 rounded-3';
            status.textContent = data.error || 'Terjadi kesalahan';
            return;
        }

        // ── Isi field form ──
        const set = (id, val) => { const el = document.getElementById(id); if (el && val) el.value = val; };
        set('nama_jurnal',   data.nama_jurnal);
        set('jenis_jurnal',  data.jenis_jurnal);
        set('meta_penulis',  data.penulis);
        set('meta_judul',    data.judul_artikel);
        set('meta_vol',      data.volume);
        set('meta_no',       data.nomor);
        set('meta_hal',      data.halaman);
        set('meta_doi',      data.doi);
        set('tahun',         data.tahun);

        // Buka panel metadata agar user bisa melihat
        const metaBody = document.getElementById('metaBody');
        if (metaBody && metaBody.style.display === 'none') toggleMeta();

        // Auto-generate semua format sitasi
        if (data.penulis && data.judul_artikel) {
            setTimeout(() => generateAllFormats(), 100);
        }

        // Hitung field yang terisi
        const filled = [data.judul_artikel, data.penulis, data.nama_jurnal, data.tahun, data.volume].filter(Boolean).length;

        status.className = 'mt-2 alert alert-success py-2 rounded-3';
        status.innerHTML = `<i class="bi bi-check2-circle me-1"></i>
            Berhasil! <strong>${filled} field</strong> terisi otomatis
            ${data.judul_artikel ? `— <em>${data.judul_artikel.substring(0,60)}${data.judul_artikel.length>60?'…':''}</em>` : ''}`;

    } catch (e) {
        status.className = 'mt-2 alert alert-danger py-2 rounded-3';
        status.textContent = 'Error: ' + e.message;
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-cloud-download-fill me-1"></i>Ambil Data';
        status.classList.remove('d-none');
    }
}

// Enter key di input URL
document.getElementById('fetchUrl')?.addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); fetchMetadata(); }
});
</script>
@endpush
@endonce
