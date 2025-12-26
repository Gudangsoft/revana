

<?php $__env->startSection('title', 'Detail Redemption - REVANA'); ?>
<?php $__env->startSection('page-title', 'Detail Reward Redemption'); ?>

<?php $__env->startSection('sidebar'); ?>
    <a href="<?php echo e(route('admin.dashboard')); ?>" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="<?php echo e(route('admin.journals.index')); ?>" class="nav-link">
        <i class="bi bi-journal-text"></i> Jurnal
    </a>
    <a href="<?php echo e(route('admin.assignments.index')); ?>" class="nav-link">
        <i class="bi bi-clipboard-check"></i> Review Assignments
    </a>
    <a href="<?php echo e(route('admin.reviewers.index')); ?>" class="nav-link">
        <i class="bi bi-people"></i> Reviewers
    </a>
    <a href="<?php echo e(route('admin.leaderboard.index')); ?>" class="nav-link">
        <i class="bi bi-trophy-fill"></i> Leaderboard
    </a>
    <a href="<?php echo e(route('admin.redemptions.index')); ?>" class="nav-link active">
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
    <a href="<?php echo e(route('admin.redemptions.index')); ?>" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
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
        <!-- Redemption Info -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-gift"></i> Informasi Redemption
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Redemption ID:</strong>
                    </div>
                    <div class="col-md-8">
                        #<?php echo e($redemption->id); ?>

                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Status:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php if($redemption->status === 'PENDING'): ?>
                            <span class="badge bg-warning">
                                <i class="bi bi-clock"></i> Pending
                            </span>
                        <?php elseif($redemption->status === 'APPROVED'): ?>
                            <span class="badge bg-info">
                                <i class="bi bi-check"></i> Approved
                            </span>
                        <?php elseif($redemption->status === 'COMPLETED'): ?>
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Completed
                            </span>
                        <?php elseif($redemption->status === 'REJECTED'): ?>
                            <span class="badge bg-danger">
                                <i class="bi bi-x-circle"></i> Rejected
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Tanggal Request:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo e($redemption->created_at->format('d M Y H:i')); ?>

                    </div>
                </div>
                <?php if($redemption->approved_at): ?>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Tanggal Approved:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo e($redemption->approved_at->format('d M Y H:i')); ?>

                    </div>
                </div>
                <?php endif; ?>
                <?php if($redemption->completed_at): ?>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Tanggal Completed:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo e($redemption->completed_at->format('d M Y H:i')); ?>

                    </div>
                </div>
                <?php endif; ?>
                <?php if($redemption->admin_notes): ?>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Admin Notes:</strong>
                    </div>
                    <div class="col-md-8">
                        <div class="alert alert-<?php echo e($redemption->status === 'REJECTED' ? 'danger' : 'info'); ?>">
                            <?php echo e($redemption->admin_notes); ?>

                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Reviewer Info -->
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <i class="bi bi-person"></i> Informasi Reviewer
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Nama:</strong>
                    </div>
                    <div class="col-md-8">
                        <a href="<?php echo e(route('admin.reviewers.show', $redemption->user)); ?>">
                            <?php echo e($redemption->user->name); ?>

                        </a>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Email:</strong>
                    </div>
                    <div class="col-md-8">
                        <?php echo e($redemption->user->email); ?>

                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Total Points:</strong>
                    </div>
                    <div class="col-md-8">
                        <span class="badge bg-warning text-dark"><?php echo e($redemption->user->total_points); ?> pts</span>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Available Points:</strong>
                    </div>
                    <div class="col-md-8">
                        <span class="badge bg-success"><?php echo e($redemption->user->available_points); ?> pts</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reward Info -->
        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <i class="bi bi-gift-fill"></i> Informasi Reward
            </div>
            <div class="card-body">
                <h5 class="mb-3"><?php echo e($redemption->reward->name); ?></h5>
                <?php if($redemption->reward->description): ?>
                <p><?php echo e($redemption->reward->description); ?></p>
                <?php endif; ?>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Tipe:</strong>
                    </div>
                    <div class="col-md-8">
                        <span class="badge bg-secondary"><?php echo e($redemption->reward->type); ?></span>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Points Required:</strong>
                    </div>
                    <div class="col-md-8">
                        <span class="badge bg-warning text-dark"><?php echo e(number_format($redemption->reward->points_required)); ?> pts</span>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Points Used:</strong>
                    </div>
                    <div class="col-md-8">
                        <span class="badge bg-danger"><?php echo e(number_format($redemption->points_used)); ?> pts</span>
                    </div>
                </div>
                <?php if($redemption->reward->value): ?>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Value:</strong>
                    </div>
                    <div class="col-md-8">
                        Rp <?php echo e(number_format($redemption->reward->value, 0, ',', '.')); ?>

                    </div>
                </div>
                <?php endif; ?>
                <?php if($redemption->notes): ?>
                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Catatan Reviewer:</strong>
                    </div>
                    <div class="col-md-8">
                        <div class="p-2 bg-light rounded">
                            <?php echo e($redemption->notes); ?>

                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Actions -->
        <div class="card mb-3">
            <div class="card-header">
                <i class="bi bi-gear"></i> Actions
            </div>
            <div class="card-body">
                <?php if($redemption->status === 'PENDING'): ?>
                    <form action="<?php echo e(route('admin.redemptions.approve', $redemption)); ?>" method="POST" class="mb-2">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('Approve redemption ini?')">
                            <i class="bi bi-check-circle"></i> Approve
                        </button>
                    </form>
                    
                    <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="bi bi-x-circle"></i> Reject
                    </button>
                <?php endif; ?>

                <?php if($redemption->status === 'APPROVED'): ?>
                    <form action="<?php echo e(route('admin.redemptions.complete', $redemption)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-primary w-100" onclick="return confirm('Tandai sebagai selesai?')">
                            <i class="bi bi-check2-all"></i> Mark as Completed
                        </button>
                    </form>
                <?php endif; ?>

                <?php if($redemption->status === 'COMPLETED'): ?>
                    <div class="alert alert-success mb-0">
                        <i class="bi bi-check-circle"></i> Redemption telah selesai
                    </div>
                <?php endif; ?>

                <?php if($redemption->status === 'REJECTED'): ?>
                    <div class="alert alert-danger mb-0">
                        <i class="bi bi-x-circle"></i> Redemption ditolak
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
                            <strong>Requested</strong>
                            <br>
                            <small><?php echo e($redemption->created_at->format('d M Y H:i')); ?></small>
                        </div>
                    </div>
                    <?php if($redemption->approved_at): ?>
                    <div class="timeline-item">
                        <i class="bi bi-check text-info"></i>
                        <div>
                            <strong>Approved</strong>
                            <br>
                            <small><?php echo e($redemption->approved_at->format('d M Y H:i')); ?></small>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if($redemption->completed_at): ?>
                    <div class="timeline-item">
                        <i class="bi bi-check-circle text-success"></i>
                        <div>
                            <strong>Completed</strong>
                            <br>
                            <small><?php echo e($redemption->completed_at->format('d M Y H:i')); ?></small>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Redemption</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo e(route('admin.redemptions.reject', $redemption)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="admin_notes" rows="5" required placeholder="Jelaskan alasan penolakan..."></textarea>
                    </div>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> Points akan dikembalikan ke reviewer.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Reject Redemption</button>
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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\LPKD-APJI\REVANA\resources\views/admin/redemptions/show.blade.php ENDPATH**/ ?>