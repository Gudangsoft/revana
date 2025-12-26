

<?php $__env->startSection('title', 'Detail Assignment - REVANA'); ?>
<?php $__env->startSection('page-title', 'Detail Review Assignment'); ?>

<?php $__env->startSection('sidebar'); ?>
    <a href="<?php echo e(route('admin.dashboard')); ?>" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="<?php echo e(route('admin.journals.index')); ?>" class="nav-link">
        <i class="bi bi-journal-text"></i> Jurnal
    </a>
    <a href="<?php echo e(route('admin.assignments.index')); ?>" class="nav-link active">
        <i class="bi bi-clipboard-check"></i> Review Assignments
    </a>
    <a href="<?php echo e(route('admin.reviewers.index')); ?>" class="nav-link">
        <i class="bi bi-people"></i> Reviewers
    </a>
    <a href="<?php echo e(route('admin.leaderboard.index')); ?>" class="nav-link">
        <i class="bi bi-trophy-fill"></i> Leaderboard
    </a>
    <a href="<?php echo e(route('admin.redemptions.index')); ?>" class="nav-link">
        <i class="bi bi-gift"></i> Reward Redemptions
    </a>
    <a href="<?php echo e(route('admin.points.index')); ?>" class="nav-link">
        <i class="bi bi-coin"></i> Point Management
    </a>
    <a href="<?php echo e(route('admin.rewards.index')); ?>" class="nav-link">
        <i class="bi bi-trophy"></i> Reward Management
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-3">
    <a href="<?php echo e(route('admin.assignments.index')); ?>" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali ke Daftar
    </a>
</div>

<?php if(session('success')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle"></i> <?php echo e(session('success')); ?>

    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if(session('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle"></i> <?php echo e(session('error')); ?>

    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <!-- Assignment Info -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-clipboard-check"></i> Informasi Assignment
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Assignment ID:</strong>
                    </div>
                    <div class="col-md-8">
                        #<?php echo e($assignment->id); ?>

                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Status:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php if($assignment->status === 'PENDING'): ?>
                            <span class="badge bg-warning">
                                <i class="bi bi-clock"></i> Pending
                            </span>
                        <?php elseif($assignment->status === 'ACCEPTED'): ?>
                            <span class="badge bg-info">
                                <i class="bi bi-check"></i> Accepted
                            </span>
                        <?php elseif($assignment->status === 'REJECTED'): ?>
                            <span class="badge bg-danger">
                                <i class="bi bi-x-circle"></i> Rejected
                            </span>
                        <?php elseif($assignment->status === 'SUBMITTED'): ?>
                            <span class="badge bg-primary">
                                <i class="bi bi-send"></i> Submitted
                            </span>
                        <?php elseif($assignment->status === 'APPROVED'): ?>
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Approved
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Assigned By:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo e($assignment->assignedBy->name); ?>

                        <br>
                        <small class="text-muted"><?php echo e($assignment->created_at->format('d M Y H:i')); ?></small>
                    </div>
                </div>
                <?php if($assignment->accepted_at): ?>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Accepted At:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo e($assignment->accepted_at->format('d M Y H:i')); ?>

                    </div>
                </div>
                <?php endif; ?>
                <?php if($assignment->submitted_at): ?>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Submitted At:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo e($assignment->submitted_at->format('d M Y H:i')); ?>

                    </div>
                </div>
                <?php endif; ?>
                <?php if($assignment->approved_at): ?>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Approved At:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo e($assignment->approved_at->format('d M Y H:i')); ?>

                    </div>
                </div>
                <?php endif; ?>
                <?php if($assignment->rejection_reason): ?>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Rejection Reason:</strong>
                    </div>
                    <div class="col-md-8">
                        <div class="alert alert-danger">
                            <?php echo e($assignment->rejection_reason); ?>

                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Journal Info -->
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <i class="bi bi-journal-text"></i> Informasi Jurnal
            </div>
            <div class="card-body">
                <h5 class="mb-3"><?php echo e($assignment->journal->title); ?></h5>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Authors:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo e($assignment->journal->authors); ?>

                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Akreditasi:</strong>
                    </div>
                    <div class="col-md-8">
                        <span class="badge bg-secondary"><?php echo e($assignment->journal->accreditation); ?></span>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Points Reward:</strong>
                    </div>
                    <div class="col-md-8">
                        <span class="badge bg-warning text-dark"><?php echo e($assignment->journal->points); ?> Points</span>
                    </div>
                </div>
                <?php if($assignment->journal->abstract): ?>
                <div class="row mt-3">
                    <div class="col-12">
                        <strong>Abstract:</strong>
                        <p class="mt-2"><?php echo e($assignment->journal->abstract); ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Reviewer Info -->
        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <i class="bi bi-person"></i> Informasi Reviewer
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Nama:</strong>
                    </div>
                    <div class="col-md-8">
                        <a href="<?php echo e(route('admin.reviewers.show', $assignment->reviewer)); ?>">
                            <?php echo e($assignment->reviewer->name); ?>

                        </a>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Email:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo e($assignment->reviewer->email); ?>

                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Total Reviews:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo e($assignment->reviewer->completed_reviews); ?> completed
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Total Points:</strong>
                    </div>
                    <div class="col-md-8">
                        <span class="badge bg-warning text-dark"><?php echo e($assignment->reviewer->total_points); ?> Points</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Review Result -->
        <?php if($assignment->reviewResult): ?>
        <div class="card mb-3">
            <div class="card-header bg-warning">
                <i class="bi bi-file-text"></i> Hasil Review
            </div>
            <div class="card-body">
                <?php if($assignment->reviewResult->file_path): ?>
                <div class="mb-3">
                    <strong>Link Google Drive:</strong>
                    <div class="mt-2">
                        <a href="<?php echo e($assignment->reviewResult->file_path); ?>" 
                           class="btn btn-primary btn-sm" 
                           target="_blank">
                            <i class="bi bi-box-arrow-up-right"></i> Buka File Review di Google Drive
                        </a>
                    </div>
                    <div class="mt-2">
                        <input type="text" class="form-control form-control-sm" 
                               value="<?php echo e($assignment->reviewResult->file_path); ?>" 
                               readonly onclick="this.select()">
                        <small class="text-muted">Klik untuk copy link</small>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if($assignment->reviewResult->notes): ?>
                <div class="mb-3">
                    <strong>Catatan Review:</strong>
                    <div class="mt-2 p-3 bg-light rounded border">
                        <?php echo nl2br(e($assignment->reviewResult->notes)); ?>

                    </div>
                </div>
                <?php endif; ?>
                
                <?php if($assignment->reviewResult->admin_feedback): ?>
                <div class="mb-3">
                    <strong>Admin Feedback:</strong>
                    <div class="alert alert-info mt-2">
                        <?php echo nl2br(e($assignment->reviewResult->admin_feedback)); ?>

                    </div>
                </div>
                <?php endif; ?>

                <div class="mt-3">
                    <small class="text-muted">
                        <i class="bi bi-clock"></i> Submitted: <?php echo e($assignment->reviewResult->created_at->format('d M Y H:i')); ?>

                    </small>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="card mb-3">
            <div class="card-header bg-secondary text-white">
                <i class="bi bi-file-text"></i> Hasil Review
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle"></i> Reviewer belum mengirimkan hasil review.
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-md-4">
        <!-- Actions -->
        <div class="card mb-3">
            <div class="card-header">
                <i class="bi bi-gear"></i> Actions
            </div>
            <div class="card-body">
                <?php if($assignment->status === 'SUBMITTED'): ?>
                    <form action="<?php echo e(route('admin.assignments.approve', $assignment)); ?>" method="POST" class="mb-2">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('Approve review ini dan berikan points?')">
                            <i class="bi bi-check-circle"></i> Approve Review
                        </button>
                    </form>
                    
                    <button type="button" class="btn btn-warning w-100 mt-2" data-bs-toggle="modal" data-bs-target="#revisionModal">
                        <i class="bi bi-arrow-clockwise"></i> Request Revision
                    </button>
                <?php endif; ?>

                <?php if($assignment->status === 'PENDING'): ?>
                    <form action="<?php echo e(route('admin.assignments.destroy', $assignment)); ?>" method="POST" onsubmit="return confirm('Yakin ingin menghapus assignment ini?')">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash"></i> Hapus Assignment
                        </button>
                    </form>
                <?php endif; ?>

                <?php if($assignment->status === 'APPROVED'): ?>
                    <div class="alert alert-success mb-0">
                        <i class="bi bi-check-circle"></i> Review sudah disetujui dan points telah diberikan.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Timeline -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history"></i> Timeline
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <i class="bi bi-plus-circle text-primary"></i>
                        <div>
                            <strong>Created</strong>
                            <br>
                            <small><?php echo e($assignment->created_at->format('d M Y H:i')); ?></small>
                        </div>
                    </div>
                    <?php if($assignment->accepted_at): ?>
                    <div class="timeline-item">
                        <i class="bi bi-check text-info"></i>
                        <div>
                            <strong>Accepted</strong>
                            <br>
                            <small><?php echo e($assignment->accepted_at->format('d M Y H:i')); ?></small>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if($assignment->submitted_at): ?>
                    <div class="timeline-item">
                        <i class="bi bi-send text-primary"></i>
                        <div>
                            <strong>Submitted</strong>
                            <br>
                            <small><?php echo e($assignment->submitted_at->format('d M Y H:i')); ?></small>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if($assignment->approved_at): ?>
                    <div class="timeline-item">
                        <i class="bi bi-check-circle text-success"></i>
                        <div>
                            <strong>Approved</strong>
                            <br>
                            <small><?php echo e($assignment->approved_at->format('d M Y H:i')); ?></small>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Revision Modal -->
<div class="modal fade" id="revisionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Revision</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo e(route('admin.assignments.revision', $assignment)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Admin Feedback <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="admin_feedback" rows="5" required placeholder="Jelaskan revisi yang diperlukan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Kirim Request Revision</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
    padding-left: 20px;
}

.timeline-item:not(:last-child):before {
    content: '';
    position: absolute;
    left: 8px;
    top: 20px;
    height: 100%;
    width: 2px;
    background: #dee2e6;
}

.timeline-item i {
    position: absolute;
    left: 0;
    top: 2px;
    font-size: 1.2rem;
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\LPKD-APJI\REVANA\resources\views/admin/assignments/show.blade.php ENDPATH**/ ?>