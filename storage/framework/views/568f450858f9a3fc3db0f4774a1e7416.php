

<?php $__env->startSection('title', 'Point Management - REVANA'); ?>
<?php $__env->startSection('page-title', 'Manajemen Point'); ?>

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
    <a href="<?php echo e(route('admin.redemptions.index')); ?>" class="nav-link">
        <i class="bi bi-gift"></i> Reward Redemptions
    </a>
    <a href="<?php echo e(route('admin.points.index')); ?>" class="nav-link active">
        <i class="bi bi-coin"></i> Point Management
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row mb-3">
    <div class="col">
        <a href="<?php echo e(route('admin.points.create')); ?>" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Tambah/Kurangi Point
        </a>
    </div>
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

<div class="card">
    <div class="card-header">
        <i class="bi bi-clock-history"></i> Riwayat Point
    </div>
    <div class="card-body">
        <?php if($pointHistories->isEmpty()): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Belum ada riwayat point.
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tanggal</th>
                        <th>Reviewer</th>
                        <th>Tipe</th>
                        <th>Deskripsi</th>
                        <th>Points</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $pointHistories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $history): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($history->id); ?></td>
                        <td>
                            <?php echo e($history->created_at->format('d M Y H:i')); ?>

                            <br>
                            <small class="text-muted"><?php echo e($history->created_at->diffForHumans()); ?></small>
                        </td>
                        <td>
                            <div class="fw-bold"><?php echo e($history->user->name); ?></div>
                            <small class="text-muted"><?php echo e($history->user->email); ?></small>
                        </td>
                        <td>
                            <?php if($history->type === 'EARNED'): ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-arrow-up-circle"></i> Earned
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger">
                                    <i class="bi bi-arrow-down-circle"></i> Redeemed
                                </span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo e($history->description); ?></td>
                        <td>
                            <?php if($history->type === 'EARNED'): ?>
                                <span class="text-success fw-bold">+<?php echo e(number_format($history->points)); ?></span>
                            <?php else: ?>
                                <span class="text-danger fw-bold">-<?php echo e(number_format($history->points)); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if(!$history->review_assignment_id && !$history->reward_redemption_id): ?>
                            <form action="<?php echo e(route('admin.points.destroy', $history)); ?>" method="POST" 
                                  onsubmit="return confirm('Hapus point history ini? Points akan dikembalikan.')">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            <?php else: ?>
                                <small class="text-muted">Auto</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-3">
            <?php echo e($pointHistories->links()); ?>

        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\LPKD-APJI\REVANA\resources\views/admin/points/index.blade.php ENDPATH**/ ?>