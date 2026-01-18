<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JournalMaster;
use App\Models\JournalSlot;
use App\Models\Submission;
use App\Models\SubmissionHistory;
use App\Models\User;
use Carbon\Carbon;

class JournalManagementSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create users for dummy data
        $users = User::all();
        if ($users->isEmpty()) {
            // Create dummy users if not exists
            $users = collect();
            for ($i = 1; $i <= 5; $i++) {
                $users->push(User::create([
                    'name' => 'Petugas ' . $i,
                    'email' => 'petugas' . $i . '@revana.test',
                    'password' => bcrypt('password'),
                ]));
            }
        }

        // ============================
        // JOURNAL MASTERS
        // ============================
        $journals = [
            [
                'nama_jurnal' => 'Jurnal Teknik Sipil Indonesia',
                'publisher' => 'Universitas Indonesia',
                'link_jurnal' => 'https://jurnal.ui.ac.id/jtsi',
                'accreditation' => 'SINTA 1',
                'is_active' => true,
            ],
            [
                'nama_jurnal' => 'Indonesian Journal of Civil Engineering',
                'publisher' => 'Institut Teknologi Bandung',
                'link_jurnal' => 'https://journals.itb.ac.id/ijce',
                'accreditation' => 'SINTA 2',
                'is_active' => true,
            ],
            [
                'nama_jurnal' => 'Jurnal Infrastruktur dan Konstruksi',
                'publisher' => 'Universitas Gadjah Mada',
                'link_jurnal' => 'https://jurnal.ugm.ac.id/jik',
                'accreditation' => 'SINTA 2',
                'is_active' => true,
            ],
            [
                'nama_jurnal' => 'Journal of Transportation Engineering',
                'publisher' => 'Institut Teknologi Sepuluh Nopember',
                'link_jurnal' => 'https://jte.its.ac.id',
                'accreditation' => 'SINTA 3',
                'is_active' => true,
            ],
            [
                'nama_jurnal' => 'Jurnal Rekayasa Sipil',
                'publisher' => 'Universitas Brawijaya',
                'link_jurnal' => 'https://jrs.ub.ac.id',
                'accreditation' => 'SINTA 3',
                'is_active' => true,
            ],
            [
                'nama_jurnal' => 'Jurnal Teknik Jalan Raya',
                'publisher' => 'Politeknik Negeri Jakarta',
                'link_jurnal' => 'https://jtjr.pnj.ac.id',
                'accreditation' => 'SINTA 4',
                'is_active' => true,
            ],
            [
                'nama_jurnal' => 'Civil Engineering Research Journal',
                'publisher' => 'Universitas Diponegoro',
                'link_jurnal' => 'https://cerj.undip.ac.id',
                'accreditation' => 'SINTA 4',
                'is_active' => false,
            ],
        ];

        $createdJournals = [];
        foreach ($journals as $journal) {
            $journal['created_by'] = $users->first()->id;
            $createdJournals[] = JournalMaster::create($journal);
        }

        $this->command->info('Created ' . count($createdJournals) . ' Journal Masters');

        // ============================
        // JOURNAL SLOTS
        // ============================
        $slots = [];
        foreach ($createdJournals as $journal) {
            if (!$journal->is_active) continue;
            
            // Create slots for 2025 and 2026
            foreach ([2025, 2026] as $year) {
                $volumes = $year == 2025 ? [10, 10] : [11, 11];
                $issues = [
                    ['nomor' => 1, 'bulan' => 'Januari-Juni', 'volume' => $volumes[0]],
                    ['nomor' => 2, 'bulan' => 'Juli-Desember', 'volume' => $volumes[1]],
                ];
                
                foreach ($issues as $issue) {
                    $jumlahSlot = rand(8, 15);
                    $slotTerpakai = $year == 2025 ? rand(5, $jumlahSlot) : rand(0, 5);
                    
                    $slots[] = JournalSlot::create([
                        'journal_master_id' => $journal->id,
                        'volume' => $issue['volume'],
                        'nomor' => $issue['nomor'],
                        'bulan' => $issue['bulan'],
                        'tahun' => $year,
                        'jumlah_slot' => $jumlahSlot,
                        'slot_terpakai' => $slotTerpakai,
                        'created_by' => $users->first()->id,
                        'is_active' => true,
                    ]);
                }
            }
        }

        $this->command->info('Created ' . count($slots) . ' Journal Slots');

        // ============================
        // SUBMISSIONS
        // ============================
        $artikelTitles = [
            'Analisis Stabilitas Lereng pada Konstruksi Jalan Tol',
            'Pengaruh Fly Ash terhadap Kuat Tekan Beton',
            'Studi Perencanaan Drainase Perkotaan',
            'Evaluasi Kinerja Perkerasan Lentur',
            'Pemodelan Struktur Jembatan Bentang Panjang',
            'Analisis Gempa pada Gedung Bertingkat Tinggi',
            'Optimasi Sistem Transportasi Publik',
            'Studi Kelayakan Pembangunan Bendungan',
            'Pengaruh Beban Lalu Lintas terhadap Perkerasan Jalan',
            'Perencanaan Geometrik Jalan Raya',
            'Analisis Kapasitas Simpang Bersinyal',
            'Studi Kualitas Air pada Sistem Distribusi',
            'Perencanaan Struktur Beton Prategang',
            'Evaluasi Kinerja Jembatan Eksisting',
            'Analisis Risiko Bencana Longsor',
            'Studi Pengendalian Banjir Sungai',
            'Optimasi Desain Pondasi Tiang Pancang',
            'Pengaruh Temperatur pada Perkerasan Aspal',
            'Analisis Keandalan Struktur Bangunan',
            'Studi Manajemen Proyek Konstruksi',
        ];

        $penulisList = [
            ['nama' => 'Dr. Ahmad Fauzi, M.T.', 'hp' => '08123456701'],
            ['nama' => 'Prof. Bambang Sutrisno', 'hp' => '08123456702'],
            ['nama' => 'Ir. Candra Wijaya, M.Sc.', 'hp' => '08123456703'],
            ['nama' => 'Dr. Dewi Anggraini', 'hp' => '08123456704'],
            ['nama' => 'Eko Prasetyo, S.T., M.T.', 'hp' => '08123456705'],
            ['nama' => 'Dr. Fajar Nugroho', 'hp' => '08123456706'],
            ['nama' => 'Ir. Gita Paramita, Ph.D.', 'hp' => '08123456707'],
            ['nama' => 'Hendra Kusuma, M.Eng.', 'hp' => '08123456708'],
            ['nama' => 'Dr. Indah Permatasari', 'hp' => '08123456709'],
            ['nama' => 'Joko Santoso, S.T., M.T.', 'hp' => '08123456710'],
        ];

        $picMarketing = ['Rina', 'Budi', 'Siti', 'Agus', 'Maya'];

        $submissions = [];
        $submissionCount = 0;

        // Create submissions with various statuses
        $statusDistribution = [
            'SUBMITTED' => 3,
            'EDITOR1_PROCESS' => 2,
            'AUTHOR1_PROCESS' => 2,
            'EDITOR2_PROCESS' => 2,
            'REVIEWER1_PROCESS' => 3,
            'REVIEWER2_PROCESS' => 2,
            'EDITOR3_PROCESS' => 2,
            'AUTHOR2_PROCESS' => 2,
            'PRODUCTION_PROCESS' => 2,
            'PUBLISHED' => 5,
            'REJECTED' => 1,
        ];

        foreach ($statusDistribution as $status => $count) {
            for ($i = 0; $i < $count; $i++) {
                $slot = $slots[array_rand($slots)];
                $penulis = $penulisList[array_rand($penulisList)];
                $title = $artikelTitles[array_rand($artikelTitles)];
                
                $tanggalSubmit = Carbon::now()->subDays(rand(1, 90));
                
                $data = [
                    'journal_slot_id' => $slot->id,
                    'id_artikel' => 'ART-' . date('Ymd', strtotime($tanggalSubmit)) . '-' . str_pad(++$submissionCount, 3, '0', STR_PAD_LEFT),
                    'judul_artikel' => $title,
                    'link_artikel' => 'https://drive.google.com/file/d/' . strtoupper(substr(md5(rand()), 0, 20)),
                    'nama_penulis' => $penulis['nama'],
                    'no_hp_penulis' => $penulis['hp'],
                    'username_author' => 'author_' . strtolower(explode(' ', $penulis['nama'])[1] ?? 'user') . rand(10, 99),
                    'password_author' => 'Pass' . rand(1000, 9999),
                    'pic_marketing' => $picMarketing[array_rand($picMarketing)],
                    'petugas_submit_id' => $users->random()->id,
                    'tanggal_submit' => $tanggalSubmit,
                    'status' => $status,
                    'created_by' => $users->first()->id,
                ];

                // Add workflow data based on status progression
                $statusOrder = ['SUBMITTED', 'EDITOR1_PROCESS', 'AUTHOR1_PROCESS', 'EDITOR2_PROCESS', 'REVIEWER1_PROCESS', 'REVIEWER2_PROCESS', 'EDITOR3_PROCESS', 'AUTHOR2_PROCESS', 'PRODUCTION_PROCESS', 'PUBLISHED'];
                $currentStatusIndex = array_search($status, $statusOrder);
                
                if ($currentStatusIndex === false && $status === 'REJECTED') {
                    $currentStatusIndex = rand(2, 6); // Rejected at some middle stage
                }

                // Editor 1
                if ($currentStatusIndex >= 1) {
                    $data['petugas_editor1_id'] = $users->random()->id;
                    $data['username_editor'] = 'editor_' . rand(100, 999);
                    $data['password_editor'] = 'EdPass' . rand(100, 999);
                    if ($currentStatusIndex >= 2) {
                        $data['editor1_valid'] = true;
                        $data['editor1_validated_at'] = $tanggalSubmit->copy()->addDays(rand(1, 3));
                    }
                }

                // Author 1
                if ($currentStatusIndex >= 2) {
                    $data['petugas_author1_id'] = $users->random()->id;
                    if ($currentStatusIndex >= 3) {
                        $data['author1_valid'] = true;
                        $data['author1_validated_at'] = $tanggalSubmit->copy()->addDays(rand(4, 7));
                    }
                }

                // Editor 2
                if ($currentStatusIndex >= 3) {
                    $data['petugas_editor2_id'] = $users->random()->id;
                    if ($currentStatusIndex >= 4) {
                        $data['editor2_valid'] = true;
                        $data['editor2_validated_at'] = $tanggalSubmit->copy()->addDays(rand(8, 10));
                    }
                }

                // Reviewer 1
                if ($currentStatusIndex >= 4) {
                    $data['petugas_reviewer1_id'] = $users->random()->id;
                    $data['username_reviewer1'] = 'reviewer1_' . rand(100, 999);
                    $data['password_reviewer1'] = 'Rev1Pass' . rand(100, 999);
                    $data['catatan_reviewer1'] = 'Artikel sudah cukup baik, perlu perbaikan minor pada metodologi.';
                    if ($currentStatusIndex >= 5) {
                        $data['reviewer1_valid'] = true;
                        $data['reviewer1_validated_at'] = $tanggalSubmit->copy()->addDays(rand(15, 20));
                    }
                }

                // Reviewer 2
                if ($currentStatusIndex >= 5) {
                    $data['petugas_reviewer2_id'] = $users->random()->id;
                    $data['username_reviewer2'] = 'reviewer2_' . rand(100, 999);
                    $data['password_reviewer2'] = 'Rev2Pass' . rand(100, 999);
                    $data['catatan_reviewer2'] = 'Hasil penelitian menarik, perlu tambahan referensi terbaru.';
                    if ($currentStatusIndex >= 6) {
                        $data['reviewer2_valid'] = true;
                        $data['reviewer2_validated_at'] = $tanggalSubmit->copy()->addDays(rand(21, 28));
                    }
                }

                // Editor 3
                if ($currentStatusIndex >= 6) {
                    $data['petugas_editor3_id'] = $users->random()->id;
                    if ($currentStatusIndex >= 7) {
                        $data['editor3_valid'] = true;
                        $data['editor3_validated_at'] = $tanggalSubmit->copy()->addDays(rand(29, 35));
                    }
                }

                // Author 2
                if ($currentStatusIndex >= 7) {
                    $data['petugas_author2_id'] = $users->random()->id;
                    if ($currentStatusIndex >= 8) {
                        $data['author2_valid'] = true;
                        $data['author2_validated_at'] = $tanggalSubmit->copy()->addDays(rand(36, 42));
                    }
                }

                // Production
                if ($currentStatusIndex >= 8) {
                    $data['petugas_production_id'] = $users->random()->id;
                    if ($currentStatusIndex >= 9) {
                        $data['production_valid'] = true;
                        $data['production_validated_at'] = $tanggalSubmit->copy()->addDays(rand(43, 50));
                        $data['link_publish'] = 'https://doi.org/10.1234/jurnal.' . date('Y') . '.' . rand(1000, 9999);
                    }
                }

                $submission = Submission::create($data);
                $submissions[] = $submission;

                // Create histories for this submission
                $this->createHistoriesForSubmission($submission, $users, $currentStatusIndex);
            }
        }

        $this->command->info('Created ' . count($submissions) . ' Submissions');

        // Count histories
        $historyCount = SubmissionHistory::count();
        $this->command->info('Created ' . $historyCount . ' Submission Histories');
    }

    private function createHistoriesForSubmission($submission, $users, $currentStatusIndex)
    {
        $baseDate = $submission->tanggal_submit;
        $dayOffset = 0;

        // Submit history
        SubmissionHistory::create([
            'submission_id' => $submission->id,
            'step' => 'submit',
            'action' => 'submitted',
            'user_id' => $submission->petugas_submit_id,
            'notes' => 'Artikel baru disubmit',
            'data' => json_encode(['judul' => $submission->judul_artikel]),
            'created_at' => $baseDate,
        ]);

        $stepsWithHistories = [
            1 => ['step' => 'editor1', 'petugas_id' => $submission->petugas_editor1_id, 'valid' => $submission->editor1_valid],
            2 => ['step' => 'author1', 'petugas_id' => $submission->petugas_author1_id, 'valid' => $submission->author1_valid],
            3 => ['step' => 'editor2', 'petugas_id' => $submission->petugas_editor2_id, 'valid' => $submission->editor2_valid],
            4 => ['step' => 'reviewer1', 'petugas_id' => $submission->petugas_reviewer1_id, 'valid' => $submission->reviewer1_valid],
            5 => ['step' => 'reviewer2', 'petugas_id' => $submission->petugas_reviewer2_id, 'valid' => $submission->reviewer2_valid],
            6 => ['step' => 'editor3', 'petugas_id' => $submission->petugas_editor3_id, 'valid' => $submission->editor3_valid],
            7 => ['step' => 'author2', 'petugas_id' => $submission->petugas_author2_id, 'valid' => $submission->author2_valid],
            8 => ['step' => 'production', 'petugas_id' => $submission->petugas_production_id, 'valid' => $submission->production_valid],
        ];

        foreach ($stepsWithHistories as $index => $stepData) {
            if ($currentStatusIndex >= $index && $stepData['petugas_id']) {
                $dayOffset += rand(1, 3);
                $petugas = User::find($stepData['petugas_id']);

                // Assigned
                SubmissionHistory::create([
                    'submission_id' => $submission->id,
                    'step' => $stepData['step'],
                    'action' => 'assigned',
                    'user_id' => $users->random()->id,
                    'notes' => 'Ditugaskan ke ' . ($petugas->name ?? 'Petugas'),
                    'created_at' => $baseDate->copy()->addDays($dayOffset),
                ]);

                // Add some revisions randomly (30% chance)
                if (rand(1, 100) <= 30 && in_array($stepData['step'], ['editor1', 'reviewer1', 'reviewer2', 'author1', 'author2'])) {
                    $revisionCount = rand(1, 2);
                    for ($r = 1; $r <= $revisionCount; $r++) {
                        $dayOffset += rand(1, 2);
                        
                        // Revision request
                        SubmissionHistory::create([
                            'submission_id' => $submission->id,
                            'step' => $stepData['step'],
                            'action' => 'revision_request',
                            'user_id' => $users->random()->id,
                            'notes' => $this->getRandomRevisionNote(),
                            'revision_number' => $r,
                            'created_at' => $baseDate->copy()->addDays($dayOffset),
                        ]);

                        $dayOffset += rand(1, 3);

                        // Revision submit
                        SubmissionHistory::create([
                            'submission_id' => $submission->id,
                            'step' => $stepData['step'],
                            'action' => 'revision_submit',
                            'user_id' => $stepData['petugas_id'],
                            'notes' => 'Revisi telah dikerjakan sesuai catatan.',
                            'revision_number' => $r,
                            'created_at' => $baseDate->copy()->addDays($dayOffset),
                        ]);
                    }
                }

                // Approved (if valid)
                if ($stepData['valid']) {
                    $dayOffset += rand(1, 2);
                    SubmissionHistory::create([
                        'submission_id' => $submission->id,
                        'step' => $stepData['step'],
                        'action' => 'approved',
                        'user_id' => $users->random()->id,
                        'notes' => ucfirst($stepData['step']) . ' divalidasi',
                        'created_at' => $baseDate->copy()->addDays($dayOffset),
                    ]);
                }
            }
        }
    }

    private function getRandomRevisionNote()
    {
        $notes = [
            'Perlu perbaikan pada bagian metodologi penelitian.',
            'Harap tambahkan referensi yang lebih relevan dan terbaru.',
            'Format penulisan belum sesuai template jurnal.',
            'Tabel dan grafik perlu diperbaiki kualitasnya.',
            'Abstrak perlu dipersingkat dan lebih fokus.',
            'Kesimpulan perlu diperkuat dengan data pendukung.',
            'Harap periksa kembali perhitungan statistik.',
            'Perlu penjelasan lebih detail pada bagian hasil.',
            'Daftar pustaka belum lengkap.',
            'Layout gambar perlu diperbaiki.',
        ];
        return $notes[array_rand($notes)];
    }
}
