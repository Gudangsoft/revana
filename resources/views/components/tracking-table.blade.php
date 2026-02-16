{{-- 
    Tracking Proses Review Table Component
    Usage: <x-tracking-table :submission="$submission" />
    Optional: <x-tracking-table :submission="$submission" show-credentials="false" />
    
    This component shows the review workflow tracking table.
    Shared across admin, PIC, and marketing views.
--}}
@props(['submission', 'showCredentials' => true])

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
                        @if($showCredentials)
                        <th>Credential</th>
                        @endif
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Submit -->
                    <tr>
                        <td><strong>Submit</strong></td>
                        <td>
                            @if($submission->petugasSubmit)
                                {{ $submission->petugasSubmit->name }}
                            @elseif($submission->marketing)
                                {{ $submission->marketing->name }}
                                <small class="text-muted">(Marketing)</small>
                            @else
                                -
                            @endif
                        </td>
                        @if($showCredentials)
                        <td>
                            @if($submission->username_author && $submission->password_author)
                                <code>{{ $submission->username_author }} / {{ $submission->password_author }}</code>
                            @else
                                -
                            @endif
                        </td>
                        @endif
                        <td>
                            @if($submission->petugasSubmit || $submission->marketing)
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Valid</span>
                            @else
                                <span class="badge bg-secondary">Pending</span>
                            @endif
                        </td>
                    </tr>
                    
                    <!-- Editor 1 -->
                    <tr class="table-info">
                        <td><strong>Editor 1 (E1)</strong></td>
                        <td>{{ $submission->petugasEditor1->name ?? '-' }}</td>
                        @if($showCredentials)
                        <td>
                            @if($submission->username_editor && $submission->password_editor)
                                <code>{{ $submission->username_editor }} / {{ $submission->password_editor }}</code>
                            @else
                                -
                            @endif
                        </td>
                        @endif
                        <td>
                            @if($submission->editor1_valid)
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Valid</span>
                            @elseif($submission->petugasEditor1)
                                <span class="badge bg-warning">In Progress</span>
                            @else
                                <span class="badge bg-secondary">Pending</span>
                            @endif
                        </td>
                    </tr>
                    
                    <!-- Author 1 -->
                    <tr class="table-warning">
                        <td><strong>Author 1 (A1)</strong></td>
                        <td>{{ $submission->petugasAuthor1->name ?? '-' }}</td>
                        @if($showCredentials)
                        <td>-</td>
                        @endif
                        <td>
                            @if($submission->author1_valid)
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Valid</span>
                            @elseif($submission->petugasAuthor1)
                                <span class="badge bg-warning">In Progress</span>
                            @else
                                <span class="badge bg-secondary">Pending</span>
                            @endif
                        </td>
                    </tr>
                    
                    <!-- Editor 2 -->
                    <tr class="table-info">
                        <td><strong>Editor 2 (E2)</strong></td>
                        <td>{{ $submission->petugasEditor2->name ?? '-' }}</td>
                        @if($showCredentials)
                        <td>-</td>
                        @endif
                        <td>
                            @if($submission->editor2_valid)
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Valid</span>
                            @elseif($submission->petugasEditor2)
                                <span class="badge bg-warning">In Progress</span>
                            @else
                                <span class="badge bg-secondary">Pending</span>
                            @endif
                        </td>
                    </tr>
                    
                    <!-- Reviewer 1 -->
                    <tr class="table-primary">
                        <td><strong>Reviewer 1 (R1)</strong></td>
                        <td>{{ $submission->petugasReviewer1->name ?? '-' }}</td>
                        @if($showCredentials)
                        <td>
                            @if($submission->username_reviewer1 && $submission->password_reviewer1)
                                <code>{{ $submission->username_reviewer1 }} / {{ $submission->password_reviewer1 }}</code>
                            @else
                                -
                            @endif
                        </td>
                        @endif
                        <td>
                            @if($submission->reviewer1_valid)
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Valid</span>
                            @elseif($submission->petugasReviewer1)
                                <span class="badge bg-warning">In Progress</span>
                            @else
                                <span class="badge bg-secondary">Pending</span>
                            @endif
                        </td>
                    </tr>
                    
                    <!-- Reviewer 2 -->
                    <tr class="table-primary">
                        <td><strong>Reviewer 2 (R2)</strong></td>
                        <td>{{ $submission->petugasReviewer2->name ?? '-' }}</td>
                        @if($showCredentials)
                        <td>
                            @if($submission->username_reviewer2 && $submission->password_reviewer2)
                                <code>{{ $submission->username_reviewer2 }} / {{ $submission->password_reviewer2 }}</code>
                            @else
                                -
                            @endif
                        </td>
                        @endif
                        <td>
                            @if($submission->reviewer2_valid)
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Valid</span>
                            @elseif($submission->petugasReviewer2)
                                <span class="badge bg-warning">In Progress</span>
                            @else
                                <span class="badge bg-secondary">Pending</span>
                            @endif
                        </td>
                    </tr>
                    
                    <!-- Editor 3 -->
                    <tr class="table-info">
                        <td><strong>Editor 3 (E3)</strong></td>
                        <td>{{ $submission->petugasEditor3->name ?? '-' }}</td>
                        @if($showCredentials)
                        <td>-</td>
                        @endif
                        <td>
                            @if($submission->editor3_valid)
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Valid</span>
                            @elseif($submission->petugasEditor3)
                                <span class="badge bg-warning">In Progress</span>
                            @else
                                <span class="badge bg-secondary">Pending</span>
                            @endif
                        </td>
                    </tr>
                    
                    <!-- Author 2 -->
                    <tr class="table-warning">
                        <td><strong>Author 2 (A2)</strong></td>
                        <td>{{ $submission->petugasAuthor2->name ?? '-' }}</td>
                        @if($showCredentials)
                        <td>-</td>
                        @endif
                        <td>
                            @if($submission->author2_valid)
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Valid</span>
                            @elseif($submission->petugasAuthor2)
                                <span class="badge bg-warning">In Progress</span>
                            @else
                                <span class="badge bg-secondary">Pending</span>
                            @endif
                        </td>
                    </tr>
                    
                    <!-- Production -->
                    <tr class="table-success">
                        <td><strong>Production (P)</strong></td>
                        <td>{{ $submission->petugasProduction->name ?? '-' }}</td>
                        @if($showCredentials)
                        <td>
                            @if($submission->link_publish)
                                <a href="{{ $submission->link_publish }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-link-45deg"></i> Link Publish
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        @endif
                        <td>
                            @if($submission->production_valid || !empty($submission->link_publish))
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Published</span>
                            @elseif($submission->petugasProduction)
                                <span class="badge bg-warning">In Progress</span>
                            @else
                                <span class="badge bg-secondary">Pending</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Legend -->
        <div class="alert alert-info mt-3 mb-0">
            <strong><i class="bi bi-info-circle"></i> Keterangan:</strong>
            <ul class="mb-0 mt-1">
                <li><span class="badge bg-secondary">Pending</span> - Belum ada petugas yang ditugaskan</li>
                <li><span class="badge bg-warning">In Progress</span> - Petugas sedang mengerjakan</li>
                <li><span class="badge bg-success">Valid/Published</span> - Tahap sudah selesai</li>
            </ul>
        </div>
    </div>
</div>
