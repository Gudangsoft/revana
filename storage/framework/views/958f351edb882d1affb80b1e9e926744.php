

<?php $__env->startSection('title', 'Detail Task - REVANA'); ?>
<?php $__env->startSection('page-title', 'Detail Tugas Review'); ?>

<?php $__env->startSection('sidebar'); ?>
    <a href="<?php echo e(route('reviewer.dashboard')); ?>" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="<?php echo e(route('reviewer.tasks.index')); ?>" class="nav-link active">
        <i class="bi bi-clipboard-check"></i> My Tasks
    </a>
    <a href="<?php echo e(route('reviewer.rewards.index')); ?>" class="nav-link">
        <i class="bi bi-gift"></i> Rewards
    </a>
    <a href="<?php echo e(route('reviewer.leaderboard.index')); ?>" class="nav-link">
        <i class="bi bi-trophy-fill"></i> Leaderboard
    </a>
    <a href="<?php echo e(route('reviewer.profile.edit')); ?>" class="nav-link">
        <i class="bi bi-person-circle"></i> My Profile
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-journal-text"></i> Informasi Jurnal
            </div>
            <div class="card-body">
                <h4><?php echo e($assignment->journal->title); ?></h4>
                <hr>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Akreditasi:</strong><br>
                        <span class="badge bg-info mt-1"><?php echo e($assignment->journal->accreditation); ?></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Points Reward:</strong><br>
                        <span class="badge bg-success mt-1" style="font-size: 1.2rem;"><?php echo e($assignment->journal->points); ?> Points</span>
                    </div>
                </div>

                <div class="mb-3">
                    <strong>Link Jurnal:</strong><br>
                    <a href="<?php echo e($assignment->journal->link); ?>" target="_blank" class="btn btn-sm btn-primary mt-1">
                        <i class="bi bi-box-arrow-up-right"></i> Buka Jurnal
                    </a>
                </div>

                <div class="mb-3">
                    <strong>Status:</strong><br>
                    <?php
                        $statusColors = [
                            'PENDING' => 'warning',
                            'ACCEPTED' => 'info',
                            'REJECTED' => 'danger',
                            'ON_PROGRESS' => 'primary',
                            'SUBMITTED' => 'success',
                            'APPROVED' => 'success',
                            'REVISION' => 'secondary'
                        ];
                        $color = $statusColors[$assignment->status] ?? 'secondary';
                    ?>
                    <span class="badge bg-<?php echo e($color); ?> mt-1" style="font-size: 1rem;"><?php echo e($assignment->status); ?></span>
                </div>

                <?php if($assignment->status == 'PENDING'): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> Tugas ini menunggu respon Anda. Silakan terima atau tolak tugas ini.
                </div>
                <?php endif; ?>

                <?php if($assignment->status == 'REVISION' && $assignment->reviewResult && $assignment->reviewResult->admin_feedback): ?>
                <div class="alert alert-danger">
                    <strong><i class="bi bi-exclamation-circle"></i> Feedback Admin:</strong><br>
                    <?php echo e($assignment->reviewResult->admin_feedback); ?>

                </div>
                <?php endif; ?>

                <?php if($assignment->reviewResult): ?>
                <div class="mb-3">
                    <strong>Link Google Drive Review:</strong><br>
                    <a href="<?php echo e($assignment->reviewResult->file_path); ?>" 
                       class="btn btn-sm btn-success mt-1" 
                       target="_blank">
                        <i class="bi bi-box-arrow-up-right"></i> Buka File Review
                    </a>
                </div>

                <?php if($assignment->reviewResult->notes): ?>
                <div class="mb-3">
                    <strong>Catatan Review:</strong><br>
                    <div class="p-2 bg-light rounded border mt-1">
                        <?php echo nl2br(e($assignment->reviewResult->notes)); ?>

                    </div>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-gear"></i> Aksi
            </div>
            <div class="card-body">
                <?php if($assignment->status == 'PENDING'): ?>
                    <form action="<?php echo e(route('reviewer.tasks.accept', $assignment)); ?>" method="POST" class="mb-2">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle"></i> Terima Tugas
                        </button>
                    </form>

                    <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="bi bi-x-circle"></i> Tolak Tugas
                    </button>
                <?php endif; ?>

                <?php if($assignment->status == 'ACCEPTED'): ?>
                    <form action="<?php echo e(route('reviewer.tasks.start', $assignment)); ?>" method="POST" class="mb-2">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-play-circle"></i> Mulai Review
                        </button>
                    </form>
                <?php endif; ?>

                <?php if(in_array($assignment->status, ['ON_PROGRESS', 'REVISION'])): ?>
                    <a href="<?php echo e(route('reviewer.results.create', $assignment)); ?>" class="btn btn-success w-100">
                        <i class="bi bi-upload"></i> Upload Hasil Review
                    </a>
                <?php endif; ?>

                <?php if($assignment->status == 'SUBMITTED'): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Menunggu validasi admin
                    </div>
                <?php endif; ?>

                <?php if($assignment->status == 'APPROVED'): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> Review telah disetujui!<br>
                        <strong>+<?php echo e($assignment->journal->points); ?> Points</strong>
                    </div>
                <?php endif; ?>

                <hr>
                <a href="<?php echo e(route('reviewer.tasks.index')); ?>" class="btn btn-secondary w-100">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-info text-white">
                <i class="bi bi-clock-history"></i> Timeline
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i> 
                        <strong>Ditugaskan:</strong><br>
                        <small><?php echo e($assignment->created_at->format('d M Y H:i')); ?></small>
                    </li>
                    <?php if($assignment->accepted_at): ?>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i> 
                        <strong>Diterima:</strong><br>
                        <small><?php echo e($assignment->accepted_at->format('d M Y H:i')); ?></small>
                    </li>
                    <?php endif; ?>
                    <?php if($assignment->submitted_at): ?>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i> 
                        <strong>Disubmit:</strong><br>
                        <small><?php echo e($assignment->submitted_at->format('d M Y H:i')); ?></small>
                    </li>
                    <?php endif; ?>
                    <?php if($assignment->approved_at): ?>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i> 
                        <strong>Disetujui:</strong><br>
                        <small><?php echo e($assignment->approved_at->format('d M Y H:i')); ?></small>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tolak Tugas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo e(route('reviewer.tasks.reject', $assignment)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="rejection_reason" rows="4" required></textarea>
                        <small class="text-muted">Jelaskan alasan Anda menolak tugas ini</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak Tugas</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\LPKD-APJI\REVANA\resources\views/reviewer/tasks/show.blade.php ENDPATH**/ ?>