

<?php $__env->startSection('title', 'Detail Reviewer - REVANA'); ?>
<?php $__env->startSection('page-title', 'Detail Reviewer'); ?>

<?php $__env->startSection('sidebar'); ?>
    <a href="<?php echo e(route('admin.dashboard')); ?>" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="<?php echo e(route('admin.journals.index')); ?>" class="nav-link">
        <i class="bi bi-journal-text"></i> Journals
    </a>
    <a href="<?php echo e(route('admin.assignments.index')); ?>" class="nav-link">
        <i class="bi bi-clipboard-check"></i> Review Assignments
    </a>
    <a href="<?php echo e(route('admin.reviewers.index')); ?>" class="nav-link active">
        <i class="bi bi-people"></i> Reviewers
    </a>
    <a href="<?php echo e(route('admin.redemptions.index')); ?>" class="nav-link">
        <i class="bi bi-gift"></i> Reward Redemptions
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<!-- Back Button -->
<div class="mb-3">
    <a href="<?php echo e(route('admin.reviewers.index')); ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<!-- Profile Card -->
<div class="row">
    <div class="col-md-12">
        <div class="card" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important; border: none;">
            <div class="card-body" style="color: white !important;">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 style="color: white !important;"><i class="bi bi-person-circle"></i> <?php echo e($reviewer->name); ?></h3>
                        <p class="mb-2" style="color: rgba(255,255,255,0.9) !important;"><?php echo e($reviewer->email); ?></p>
                        <div class="d-flex gap-3 mt-3">
                            <div>
                                <h4 class="mb-0" style="color: white !important;"><?php echo e($reviewer->total_points); ?></h4>
                                <small style="color: rgba(255,255,255,0.8) !important;">Total Points</small>
                            </div>
                            <div>
                                <h4 class="mb-0" style="color: white !important;"><?php echo e($reviewer->available_points); ?></h4>
                                <small style="color: rgba(255,255,255,0.8) !important;">Available Points</small>
                            </div>
                            <div>
                                <h4 class="mb-0" style="color: white !important;"><?php echo e($reviewer->completed_reviews); ?></h4>
                                <small style="color: rgba(255,255,255,0.8) !important;">Completed Reviews</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <?php $__currentLoopData = $reviewer->badges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $badge): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span class="badge bg-warning text-dark me-1" style="font-size: 1.2rem;" title="<?php echo e($badge->description); ?>">
                            <?php echo e($badge->icon); ?> <?php echo e($badge->name); ?>

                        </span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Review Assignments -->
<div class="row mt-4">
    <!-- Statistics Cards -->
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-warning">Pending</h5>
                <h2><?php echo e($reviewer->reviewAssignments->where('status', 'pending')->count()); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-info">In Progress</h5>
                <h2><?php echo e($reviewer->reviewAssignments->whereIn('status', ['accepted', 'submitted'])->count()); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-success">Completed</h5>
                <h2><?php echo e($reviewer->reviewAssignments->where('status', 'approved')->count()); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-danger">Rejected</h5>
                <h2><?php echo e($reviewer->reviewAssignments->where('status', 'rejected')->count()); ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clipboard-check"></i> Riwayat Review Assignments
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Jurnal</th>
                                <th>Akreditasi</th>
                                <th>Points</th>
                                <th>Status</th>
                                <th>Tanggal Assignment</th>
                                <th>Tanggal Selesai</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $reviewer->reviewAssignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <strong><?php echo e(Str::limit($assignment->journal->title, 50)); ?></strong>
                                    <?php if($assignment->reviewResult): ?>
                                        <br><small class="text-muted"><i class="bi bi-file-text"></i> Review submitted</small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($assignment->journal->accreditation); ?></td>
                                <td><span class="badge bg-info"><?php echo e($assignment->journal->points); ?> pts</span></td>
                                <td>
                                    <?php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'accepted' => 'info',
                                            'rejected' => 'danger',
                                            'submitted' => 'success',
                                            'approved' => 'success',
                                        ];
                                        $color = $statusColors[$assignment->status] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo e($color); ?>"><?php echo e(ucfirst($assignment->status)); ?></span>
                                </td>
                                <td><?php echo e($assignment->created_at->format('d M Y H:i')); ?></td>
                                <td>
                                    <?php if($assignment->approved_at): ?>
                                        <?php echo e($assignment->approved_at->format('d M Y H:i')); ?>

                                    <?php elseif($assignment->submitted_at): ?>
                                        <?php echo e($assignment->submitted_at->format('d M Y H:i')); ?>

                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo e(route('admin.assignments.show', $assignment)); ?>" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">Belum ada review assignment</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Point History -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history"></i> Riwayat Points
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Tipe</th>
                                <th>Deskripsi</th>
                                <th>Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $reviewer->pointHistories()->latest()->take(20)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $history): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($history->created_at->format('d M Y H:i')); ?></td>
                                <td>
                                    <?php if($history->type == 'EARNED'): ?>
                                        <span class="badge bg-success">Earned</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Redeemed</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($history->description); ?></td>
                                <td>
                                    <?php if($history->type == 'EARNED'): ?>
                                        <span class="text-success">+<?php echo e($history->points); ?></span>
                                    <?php else: ?>
                                        <span class="text-danger">-<?php echo e($history->points); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">Belum ada riwayat points</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\LPKD-APJI\REVANA\resources\views/admin/reviewers/show.blade.php ENDPATH**/ ?>