<?php
/**
 * Debug Import Journal Slot
 * Akses via: https://portal.apji.org/debug-import-slot.php
 * 
 * Script ini akan:
 * 1. Cek apakah jurnal FUNDAMENTUM ada
 * 2. Cek slot yang sudah ada
 * 3. Tambahkan jurnal jika belum ada
 * 4. Tambahkan slot jika belum ada
 */

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\JournalMaster;
use App\Models\JournalSlot;

header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Import Slot - FUNDAMENTUM</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #333; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .info { color: #17a2b8; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #007bff; color: white; }
        .btn { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #1e7e34; }
        .section { margin: 20px 0; padding: 15px; background: #f8f9fa; border-left: 4px solid #007bff; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔍 Debug Import Slot - FUNDAMENTUM</h1>
    <p class="info">Tanggal: <?= date('Y-m-d H:i:s') ?></p>
    
    <?php
    $action = $_GET['action'] ?? 'check';
    $journalName = 'FUNDAMENTUM : Jurnal Pengabdian Multidisiplin';
    
    // ========== 1. CEK JURNAL ==========
    echo '<div class="section">';
    echo '<h2>1️⃣ Cek Jurnal di Database</h2>';
    
    $journal = JournalMaster::where('nama_jurnal', $journalName)->first();
    
    if (!$journal) {
        $journal = JournalMaster::whereRaw('LOWER(nama_jurnal) LIKE ?', ['%fundamentum%'])->first();
    }
    
    if ($journal) {
        echo '<p class="success">✓ Jurnal DITEMUKAN</p>';
        echo '<table>';
        echo '<tr><th>ID</th><td>' . $journal->id . '</td></tr>';
        echo '<tr><th>Nama</th><td>' . htmlspecialchars($journal->nama_jurnal) . '</td></tr>';
        echo '<tr><th>Kode</th><td>' . ($journal->kode_jurnal ?? '-') . '</td></tr>';
        echo '<tr><th>Publisher</th><td>' . ($journal->publisher ?? '-') . '</td></tr>';
        echo '<tr><th>Status</th><td>' . ($journal->is_active ? 'Aktif' : 'Tidak Aktif') . '</td></tr>';
        echo '</table>';
    } else {
        echo '<p class="error">✗ Jurnal TIDAK DITEMUKAN</p>';
        echo '<p>Jurnal "<strong>' . htmlspecialchars($journalName) . '</strong>" tidak ada di database.</p>';
        
        if ($action === 'add_journal') {
            try {
                $journal = JournalMaster::create([
                    'nama_jurnal' => $journalName,
                    'kode_jurnal' => 'FND' . date('Y'),
                    'publisher' => 'LPKD APJI',
                    'kategori' => 'Penelitian',
                    'jenis_jurnal' => 'Nasional',
                    'accreditation' => 1,
                    'is_active' => true,
                ]);
                echo '<p class="success">✓ Jurnal berhasil ditambahkan dengan ID: ' . $journal->id . '</p>';
                echo '<script>setTimeout(function(){ location.href = "debug-import-slot.php"; }, 2000);</script>';
            } catch (Exception $e) {
                echo '<p class="error">✗ Gagal menambahkan jurnal: ' . $e->getMessage() . '</p>';
            }
        } else {
            echo '<form method="get">';
            echo '<input type="hidden" name="action" value="add_journal">';
            echo '<button type="submit" class="btn btn-success">+ Tambahkan Jurnal Ini</button>';
            echo '</form>';
        }
    }
    echo '</div>';
    
    // ========== 2. CEK SLOT ==========
    if ($journal) {
        echo '<div class="section">';
        echo '<h2>2️⃣ Cek Slot yang Sudah Ada</h2>';
        
        $slots = JournalSlot::where('journal_master_id', $journal->id)
            ->orderBy('tahun', 'desc')
            ->orderBy('volume', 'desc')
            ->orderBy('nomor', 'desc')
            ->get();
        
        // Expected slots from Excel
        $expectedSlots = [
            ['volume' => 1, 'nomor' => 1, 'bulan' => 'Februari', 'tahun' => 2023, 'jumlah_slot' => 30],
            ['volume' => 1, 'nomor' => 2, 'bulan' => 'Mei', 'tahun' => 2023, 'jumlah_slot' => 30],
            ['volume' => 1, 'nomor' => 3, 'bulan' => 'Agustus', 'tahun' => 2023, 'jumlah_slot' => 30],
            ['volume' => 1, 'nomor' => 4, 'bulan' => 'November', 'tahun' => 2023, 'jumlah_slot' => 30],
            ['volume' => 2, 'nomor' => 1, 'bulan' => 'Februari', 'tahun' => 2024, 'jumlah_slot' => 27],
            ['volume' => 2, 'nomor' => 2, 'bulan' => 'Mei', 'tahun' => 2024, 'jumlah_slot' => 23],
            ['volume' => 2, 'nomor' => 3, 'bulan' => 'Agustus', 'tahun' => 2024, 'jumlah_slot' => 16],
            ['volume' => 2, 'nomor' => 4, 'bulan' => 'November', 'tahun' => 2024, 'jumlah_slot' => 12],
        ];
        
        if ($slots->count() > 0) {
            echo '<p class="success">✓ Ditemukan ' . $slots->count() . ' slot</p>';
            echo '<table>';
            echo '<tr><th>Kode</th><th>Vol</th><th>No</th><th>Bulan</th><th>Tahun</th><th>Jumlah</th><th>Terpakai</th><th>Status</th><th>Dibuat</th></tr>';
            foreach ($slots as $slot) {
                echo '<tr>';
                echo '<td>' . $slot->kode_slot . '</td>';
                echo '<td>' . $slot->volume . '</td>';
                echo '<td>' . $slot->nomor . '</td>';
                echo '<td>' . $slot->bulan . '</td>';
                echo '<td>' . $slot->tahun . '</td>';
                echo '<td>' . $slot->jumlah_slot . '</td>';
                echo '<td>' . $slot->slot_terpakai . '</td>';
                echo '<td>' . ($slot->is_active ? '<span class="success">Aktif</span>' : 'Non-aktif') . '</td>';
                echo '<td>' . $slot->created_at->format('Y-m-d H:i') . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            
            // Check missing slots
            echo '<h3>Cek Data dari Excel (Vol 1-2, Tahun 2023-2024)</h3>';
            $missing = [];
            foreach ($expectedSlots as $expected) {
                $exists = $slots->first(function($slot) use ($expected) {
                    return $slot->volume == $expected['volume'] 
                        && $slot->nomor == $expected['nomor'] 
                        && $slot->tahun == $expected['tahun'];
                });
                
                if (!$exists) {
                    $missing[] = $expected;
                }
            }
            
            if (count($missing) > 0) {
                echo '<p class="error">✗ Data Excel yang BELUM ada di database (' . count($missing) . ' slot):</p>';
                echo '<table>';
                echo '<tr><th>Vol</th><th>No</th><th>Bulan</th><th>Tahun</th><th>Jumlah Slot</th></tr>';
                foreach ($missing as $m) {
                    echo '<tr style="background: #ffe6e6;">';
                    echo '<td>' . $m['volume'] . '</td>';
                    echo '<td>' . $m['nomor'] . '</td>';
                    echo '<td>' . $m['bulan'] . '</td>';
                    echo '<td>' . $m['tahun'] . '</td>';
                    echo '<td>' . $m['jumlah_slot'] . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
                
                if ($action === 'add_missing') {
                    $added = 0;
                    foreach ($missing as $data) {
                        try {
                            JournalSlot::create([
                                'journal_master_id' => $journal->id,
                                'volume' => $data['volume'],
                                'nomor' => $data['nomor'],
                                'bulan' => $data['bulan'],
                                'tahun' => $data['tahun'],
                                'jumlah_slot' => $data['jumlah_slot'],
                                'slot_terpakai' => 0,
                                'is_active' => true,
                                'created_by' => 1,
                            ]);
                            $added++;
                        } catch (Exception $e) {
                            echo '<p class="error">Error: ' . $e->getMessage() . '</p>';
                        }
                    }
                    echo '<p class="success">✓ Berhasil menambahkan ' . $added . ' slot yang hilang</p>';
                    echo '<script>setTimeout(function(){ location.href = "debug-import-slot.php"; }, 2000);</script>';
                } else {
                    echo '<form method="get">';
                    echo '<input type="hidden" name="action" value="add_missing">';
                    echo '<button type="submit" class="btn btn-success">+ Tambahkan ' . count($missing) . ' Slot yang Hilang</button>';
                    echo '</form>';
                }
            } else {
                echo '<p class="success">✓ Semua data Excel sudah ada di database</p>';
            }
        } else {
            echo '<p class="warning">⚠ Belum ada slot untuk jurnal ini</p>';
            
            if ($action === 'add_slots') {
                try {
                    $slotsData = [
                        ['volume' => 1, 'nomor' => 1, 'bulan' => 'Februari', 'tahun' => 2023, 'jumlah_slot' => 30],
                        ['volume' => 1, 'nomor' => 2, 'bulan' => 'Mei', 'tahun' => 2023, 'jumlah_slot' => 30],
                        ['volume' => 1, 'nomor' => 3, 'bulan' => 'Agustus', 'tahun' => 2023, 'jumlah_slot' => 30],
                        ['volume' => 1, 'nomor' => 4, 'bulan' => 'November', 'tahun' => 2023, 'jumlah_slot' => 30],
                        ['volume' => 2, 'nomor' => 1, 'bulan' => 'Februari', 'tahun' => 2024, 'jumlah_slot' => 27],
                        ['volume' => 2, 'nomor' => 2, 'bulan' => 'Mei', 'tahun' => 2024, 'jumlah_slot' => 23],
                        ['volume' => 2, 'nomor' => 3, 'bulan' => 'Agustus', 'tahun' => 2024, 'jumlah_slot' => 16],
                        ['volume' => 2, 'nomor' => 4, 'bulan' => 'November', 'tahun' => 2024, 'jumlah_slot' => 12],
                    ];
                    
                    $added = 0;
                    foreach ($slotsData as $data) {
                        $exists = JournalSlot::where('journal_master_id', $journal->id)
                            ->where('volume', $data['volume'])
                            ->where('nomor', $data['nomor'])
                            ->where('tahun', $data['tahun'])
                            ->first();
                        
                        if (!$exists) {
                            JournalSlot::create([
                                'journal_master_id' => $journal->id,
                                'volume' => $data['volume'],
                                'nomor' => $data['nomor'],
                                'bulan' => $data['bulan'],
                                'tahun' => $data['tahun'],
                                'jumlah_slot' => $data['jumlah_slot'],
                                'slot_terpakai' => 0,
                                'is_active' => true,
                                'created_by' => 1,
                            ]);
                            $added++;
                        }
                    }
                    
                    echo '<p class="success">✓ Berhasil menambahkan ' . $added . ' slot</p>';
                    echo '<script>setTimeout(function(){ location.href = "debug-import-slot.php"; }, 2000);</script>';
                } catch (Exception $e) {
                    echo '<p class="error">✗ Gagal menambahkan slot: ' . $e->getMessage() . '</p>';
                }
            } else {
                echo '<p>Slot yang akan ditambahkan:</p>';
                echo '<ul>';
                echo '<li>Vol 1 No 1 - Februari 2023 (30 slot)</li>';
                echo '<li>Vol 1 No 2 - Mei 2023 (30 slot)</li>';
                echo '<li>Vol 1 No 3 - Agustus 2023 (30 slot)</li>';
                echo '<li>Vol 1 No 4 - November 2023 (30 slot)</li>';
                echo '<li>Vol 2 No 1 - Februari 2024 (27 slot)</li>';
                echo '<li>Vol 2 No 2 - Mei 2024 (23 slot)</li>';
                echo '<li>Vol 2 No 3 - Agustus 2024 (16 slot)</li>';
                echo '<li>Vol 2 No 4 - November 2024 (12 slot)</li>';
                echo '</ul>';
                echo '<form method="get">';
                echo '<input type="hidden" name="action" value="add_slots">';
                echo '<button type="submit" class="btn btn-success">+ Tambahkan 8 Slot Ini</button>';
                echo '</form>';
            }
        }
        echo '</div>';
    }
    
    // ========== 3. CEK SLOT TERBARU ==========
    echo '<div class="section">';
    echo '<h2>3️⃣ Slot Terbaru di Database (Semua Jurnal)</h2>';
    $latestSlots = JournalSlot::with('journalMaster')
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();
    
    if ($latestSlots->count() > 0) {
        echo '<table>';
        echo '<tr><th>Kode</th><th>Jurnal</th><th>Vol/No</th><th>Tahun</th><th>Dibuat</th></tr>';
        foreach ($latestSlots as $slot) {
            echo '<tr>';
            echo '<td>' . $slot->kode_slot . '</td>';
            echo '<td>' . htmlspecialchars($slot->journalMaster->nama_jurnal ?? '-') . '</td>';
            echo '<td>' . $slot->volume . '/' . $slot->nomor . '</td>';
            echo '<td>' . $slot->tahun . '</td>';
            echo '<td>' . $slot->created_at->format('Y-m-d H:i:s') . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo '<p class="warning">Tidak ada slot di database</p>';
    }
    echo '</div>';
    
    // ========== 4. INSTRUKSI ==========
    echo '<div class="section">';
    echo '<h2>4️⃣ Langkah Selanjutnya</h2>';
    if (!$journal) {
        echo '<ol>';
        echo '<li>Klik tombol "Tambahkan Jurnal Ini" di atas</li>';
        echo '<li>Setelah jurnal ditambahkan, halaman akan refresh otomatis</li>';
        echo '<li>Kemudian klik "Tambahkan Slot"</li>';
        echo '<li>Selesai! Data akan muncul di halaman admin</li>';
        echo '</ol>';
    } elseif ($slots->count() == 0) {
        echo '<ol>';
        echo '<li>Klik tombol "Tambahkan 8 Slot Ini" di atas</li>';
        echo '<li>Setelah berhasil, cek halaman <a href="/admin/journal-slots" target="_blank">Admin Journal Slots</a></li>';
        echo '</ol>';
    } else {
        echo '<p class="success">✓ Semua data sudah lengkap!</p>';
        echo '<p>Silakan cek halaman: <a href="/admin/journal-slots?search=FUNDAMENTUM" target="_blank">Admin Journal Slots - FUNDAMENTUM</a></p>';
        echo '<p><a href="debug-import-slot.php" class="btn">🔄 Refresh Halaman</a></p>';
    }
    echo '</div>';
    ?>
    
    <div style="margin-top: 30px; padding: 15px; background: #fff3cd; border-radius: 4px;">
        <strong>💡 Tips:</strong>
        <ul>
            <li>Hapus file ini setelah selesai debugging (security risk)</li>
            <li>Atau tambahkan password protection</li>
            <li>File location: <code>public/debug-import-slot.php</code></li>
        </ul>
    </div>
</div>
</body>
</html>
