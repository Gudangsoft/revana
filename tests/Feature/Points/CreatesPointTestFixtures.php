<?php

namespace Tests\Feature\Points;

use App\Models\JournalMaster;
use App\Models\JournalSlot;
use App\Models\Marketing;
use App\Models\Pic;
use App\Models\Submission;
use App\Models\User;

/**
 * Helper bersama untuk test seputar sistem poin PIC/Marketing — bikin data minimal
 * (User -> JournalMaster -> JournalSlot -> Submission, plus Pic/Marketing) supaya
 * tiap test tidak perlu mengulang rantai foreign key yang sama.
 */
trait CreatesPointTestFixtures
{
    protected function makeUser(): User
    {
        return User::create([
            'name' => 'Test Admin',
            'email' => 'test-admin-' . uniqid() . '@example.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }

    protected function makeJournalSlot(?User $user = null): JournalSlot
    {
        $user ??= $this->makeUser();

        $master = JournalMaster::create([
            'kode_jurnal' => 'JRN-' . uniqid(),
            'nama_jurnal' => 'Jurnal Test',
            'publisher' => 'Penerbit Test',
            'link_jurnal' => 'https://example.test/jurnal',
            'created_by' => $user->id,
        ]);

        return JournalSlot::create([
            'kode_slot' => 'SLOT-' . uniqid(),
            'journal_master_id' => $master->id,
            'volume' => '1',
            'nomor' => '1',
            'bulan' => 'Januari',
            'tahun' => 2026,
            'jumlah_slot' => 100,
            'created_by' => $user->id,
        ]);
    }

    protected function makeSubmission(array $overrides = []): Submission
    {
        $user = $this->makeUser();
        $slot = $this->makeJournalSlot($user);

        return Submission::create(array_merge([
            'kode_submit' => 'SUB-' . uniqid(),
            'journal_slot_id' => $slot->id,
            'id_artikel' => 'ART-' . uniqid(),
            'judul_artikel' => 'Judul Artikel Test',
            'created_by' => $user->id,
            'status' => 'SUBMITTED',
        ], $overrides));
    }

    protected function makePic(array $overrides = []): Pic
    {
        return Pic::create(array_merge([
            'name' => 'PIC Test ' . uniqid(),
            'is_active' => true,
            'total_points' => 0,
        ], $overrides));
    }

    protected function makeMarketing(array $overrides = []): Marketing
    {
        return Marketing::create(array_merge([
            'name' => 'Marketing Test ' . uniqid(),
            'is_active' => true,
            'total_points' => 0,
        ], $overrides));
    }
}
