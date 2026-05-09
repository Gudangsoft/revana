{{-- 
    Tracking Proses Review Table Component
    Usage: <x-tracking-table :submission="$submission" />
    Optional: <x-tracking-table :submission="$submission" show-credentials="false" />
    
    This component shows the review workflow tracking table.
    Reads visual settings from ComponentSettingService (editable via admin panel).
    Shared across admin, PIC, and marketing views.
--}}
@props(['submission', 'showCredentials' => null])

@php
    use App\Services\ComponentSettingService;
    $s = ComponentSettingService::all();

    // Allow prop override, otherwise use setting
    $showCreds = $showCredentials ?? (bool)($s['tracking_show_credentials'] ?? true);

    $validColor    = $s['tracking_valid_color']    ?? 'bg-success';
    $progressColor = $s['tracking_progress_color'] ?? 'bg-warning';
    $pendingColor  = $s['tracking_pending_color']  ?? 'bg-secondary';

    // Load assignment histories (1 query, grouped by step)
    $assignHistories = \App\Models\SubmissionHistory::where('submission_id', $submission->id)
        ->where('action', 'assigned')
        ->orderBy('created_at')
        ->get()
        ->groupBy('step');

    $assignedAt = [];
    foreach ($assignHistories as $stepKey => $records) {
        $assignedAt[$stepKey] = $records->first()->created_at;
    }

    // Load PicPointHistory sebagai fallback waktu selesai untuk data lama
    // yang *_valid = true tapi *_validated_at = null (sebelum fix tracked timestamps)
    $picPointTimes = \App\Models\PicPointHistory::where('submission_id', $submission->id)
        ->select('step', \Illuminate\Support\Facades\DB::raw('MIN(created_at) as completed_at'))
        ->groupBy('step')
        ->pluck('completed_at', 'step');

    $fmt = fn($dt) => $dt ? \Carbon\Carbon::parse($dt)->format('d/m/Y H:i') : '-';

    // Waktu selesai: utamakan *_validated_at, fallback ke PicPointHistory, fallback ke '-'
    $selesai = function($validatedAt, $stepKey) use ($fmt, $picPointTimes) {
        if ($validatedAt) return $fmt($validatedAt);
        return isset($picPointTimes[$stepKey]) ? $fmt($picPointTimes[$stepKey]) : '-';
    };

    // Submit: gunakan created_at (memiliki jam) sebagai waktu penugasan.
    // tanggal_submit adalah tipe DATE (tanpa jam) → selalu 00:00 jika dipakai langsung.
    $submitPenugasan = $submission->created_at;

    // Build steps config
    $steps = [
        [
            'key'          => 'submit',
            'label'        => 'Submit',
            'show'         => $s['tracking_show_submit'] ?? '1',
            'rowClass'     => $s['tracking_row_submit'] ?? '',
            'petugas'      => $submission->petugasSubmit,
            'petugasFallback'      => $submission->marketing,
            'petugasFallbackLabel' => '(Marketing)',
            'isValid'      => $submission->petugasSubmit || $submission->marketing,
            'hasAssignment'=> $submission->petugasSubmit || $submission->marketing,
            'credential'   => ($submission->username_author && $submission->password_author)
                ? $submission->username_author . ' / ' . $submission->password_author : null,
            'validLabel'   => 'Valid',
            'penugasan_at' => $fmt($submitPenugasan),
            'selesai_at'   => '-',
        ],
        [
            'key'          => 'editor1',
            'label'        => 'Editor 1 (E1)',
            'show'         => $s['tracking_show_editor1'] ?? '1',
            'rowClass'     => $s['tracking_row_editor1'] ?? 'table-info',
            'petugas'      => $submission->petugasEditor1,
            'isValid'      => $submission->editor1_valid,
            'hasAssignment'=> $submission->petugasEditor1,
            'credential'   => ($submission->username_editor && $submission->password_editor)
                ? $submission->username_editor . ' / ' . $submission->password_editor : null,
            'validLabel'   => 'Valid',
            'penugasan_at' => $fmt($assignedAt['editor1'] ?? null),
            'selesai_at'   => $selesai($submission->editor1_validated_at, 'editor1'),
        ],
        [
            'key'          => 'author1',
            'label'        => 'Author 1 (A1)',
            'show'         => $s['tracking_show_author1'] ?? '1',
            'rowClass'     => $s['tracking_row_author1'] ?? 'table-warning',
            'petugas'      => $submission->petugasAuthor1,
            'isValid'      => $submission->author1_valid,
            'hasAssignment'=> $submission->petugasAuthor1,
            'credential'   => null,
            'validLabel'   => 'Valid',
            'penugasan_at' => $fmt($assignedAt['author1'] ?? null),
            'selesai_at'   => $selesai($submission->author1_validated_at, 'author1'),
        ],
        [
            'key'          => 'editor2',
            'label'        => 'Editor 2 (E2)',
            'show'         => $s['tracking_show_editor2'] ?? '1',
            'rowClass'     => $s['tracking_row_editor2'] ?? 'table-info',
            'petugas'      => $submission->petugasEditor2,
            'isValid'      => $submission->editor2_valid,
            'hasAssignment'=> $submission->petugasEditor2,
            'credential'   => null,
            'validLabel'   => 'Valid',
            'penugasan_at' => $fmt($assignedAt['editor2'] ?? null),
            'selesai_at'   => $selesai($submission->editor2_validated_at, 'editor2'),
        ],
        [
            'key'          => 'reviewer1',
            'label'        => 'Reviewer 1 (R1)',
            'show'         => $s['tracking_show_reviewer1'] ?? '1',
            'rowClass'     => $s['tracking_row_reviewer1'] ?? 'table-primary',
            'petugas'      => $submission->petugasReviewer1,
            'isValid'      => $submission->reviewer1_valid,
            'hasAssignment'=> $submission->petugasReviewer1,
            'credential'   => ($submission->username_reviewer1 && $submission->password_reviewer1)
                ? $submission->username_reviewer1 . ' / ' . $submission->password_reviewer1 : null,
            'validLabel'   => 'Valid',
            'penugasan_at' => $fmt($assignedAt['reviewer1'] ?? null),
            'selesai_at'   => $selesai($submission->reviewer1_validated_at, 'reviewer1'),
        ],
        [
            'key'          => 'reviewer2',
            'label'        => 'Reviewer 2 (R2)',
            'show'         => $s['tracking_show_reviewer2'] ?? '1',
            'rowClass'     => $s['tracking_row_reviewer2'] ?? 'table-primary',
            'petugas'      => $submission->petugasReviewer2,
            'isValid'      => $submission->reviewer2_valid,
            'hasAssignment'=> $submission->petugasReviewer2,
            'credential'   => ($submission->username_reviewer2 && $submission->password_reviewer2)
                ? $submission->username_reviewer2 . ' / ' . $submission->password_reviewer2 : null,
            'validLabel'   => 'Valid',
            'penugasan_at' => $fmt($assignedAt['reviewer2'] ?? null),
            'selesai_at'   => $selesai($submission->reviewer2_validated_at, 'reviewer2'),
        ],
        [
            'key'          => 'editor3',
            'label'        => 'Editor 3 (E3)',
            'show'         => $s['tracking_show_editor3'] ?? '1',
            'rowClass'     => $s['tracking_row_editor3'] ?? 'table-info',
            'petugas'      => $submission->petugasEditor3,
            'isValid'      => $submission->editor3_valid,
            'hasAssignment'=> $submission->petugasEditor3,
            'credential'   => null,
            'validLabel'   => 'Valid',
            'penugasan_at' => $fmt($assignedAt['editor3'] ?? null),
            'selesai_at'   => $selesai($submission->editor3_validated_at, 'editor3'),
        ],
        [
            'key'          => 'author2',
            'label'        => 'Author 2 (A2)',
            'show'         => $s['tracking_show_author2'] ?? '1',
            'rowClass'     => $s['tracking_row_author2'] ?? 'table-warning',
            'petugas'      => $submission->petugasAuthor2,
            'isValid'      => $submission->author2_valid,
            'hasAssignment'=> $submission->petugasAuthor2,
            'credential'   => null,
            'validLabel'   => 'Valid',
            'penugasan_at' => $fmt($assignedAt['author2'] ?? null),
            'selesai_at'   => $selesai($submission->author2_validated_at, 'author2'),
        ],
        [
            'key'          => 'production',
            'label'        => 'Production (P)',
            'show'         => $s['tracking_show_production'] ?? '1',
            'rowClass'     => $s['tracking_row_production'] ?? 'table-success',
            'petugas'      => $submission->petugasProduction,
            'isValid'      => $submission->production_valid || !empty($submission->link_publish),
            'hasAssignment'=> $submission->petugasProduction,
            'credential'   => null,
            'isProduction' => true,
            'validLabel'   => 'Published',
            'penugasan_at' => $fmt($assignedAt['production'] ?? null),
            'selesai_at'   => $selesai($submission->production_validated_at, 'production'),
        ],
        [
            'key'          => 'validator',
            'label'        => 'Validasi (V)',
            'show'         => $s['tracking_show_validator'] ?? '1',
            'rowClass'     => $s['tracking_row_validator'] ?? 'table-success',
            'petugas'      => $submission->petugasValidator,
            'isValid'      => $submission->validator_valid,
            'hasAssignment'=> $submission->petugasValidator,
            'credential'   => null,
            'validLabel'   => 'Valid',
            'penugasan_at' => $fmt($assignedAt['validator'] ?? null),
            'selesai_at'   => $selesai($submission->validator_validated_at, 'validator'),
        ],
    ];
@endphp

<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="bi bi-diagram-3"></i> Tracking Proses Review</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                    <tr>
                        <th>Tahap</th>
                        <th>Petugas</th>
                        @if($showCreds)
                        <th>Credential</th>
                        @endif
                        <th style="min-width:120px"><i class="bi bi-clock"></i> Waktu Penugasan</th>
                        <th style="min-width:120px"><i class="bi bi-clock-history"></i> Waktu Selesai</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($steps as $step)
                        @if($step['show'])
                        <tr class="{{ $step['rowClass'] }}">
                            <td><strong>{{ $step['label'] }}</strong></td>
                            <td>
                                @if($step['petugas'])
                                    {{ $step['petugas']->name }}
                                @elseif(isset($step['petugasFallback']) && $step['petugasFallback'])
                                    {{ $step['petugasFallback']->name }}
                                    <small class="text-muted">{{ $step['petugasFallbackLabel'] ?? '' }}</small>
                                @else
                                    -
                                @endif
                            </td>
                            @if($showCreds)
                            <td>
                                @if(isset($step['isProduction']) && $submission->link_publish)
                                    <a href="{{ $submission->link_publish }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-link-45deg"></i> Link Publish
                                    </a>
                                @elseif($step['credential'])
                                    <code>{{ $step['credential'] }}</code>
                                @else
                                    -
                                @endif
                            </td>
                            @endif
                            <td class="small text-nowrap text-muted">{{ $step['penugasan_at'] }}</td>
                            <td class="small text-nowrap text-muted">{{ $step['selesai_at'] }}</td>
                            <td>
                                @if($step['isValid'])
                                    <span class="badge {{ $validColor }}"><i class="bi bi-check-circle"></i> {{ $step['validLabel'] }}</span>
                                @elseif($step['hasAssignment'])
                                    <span class="badge {{ $progressColor }}">In Progress</span>
                                @else
                                    <span class="badge {{ $pendingColor }}">Pending</span>
                                @endif
                            </td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Legend -->
        <div class="alert alert-info mt-3 mb-0">
            <strong><i class="bi bi-info-circle"></i> Keterangan:</strong>
            <ul class="mb-0 mt-1">
                <li><span class="badge {{ $pendingColor }}">Pending</span> - Belum ada petugas yang ditugaskan</li>
                <li><span class="badge {{ $progressColor }}">In Progress</span> - Petugas sedang mengerjakan</li>
                <li><span class="badge {{ $validColor }}">Valid/Published</span> - Tahap sudah selesai</li>
            </ul>
        </div>
    </div>
</div>
