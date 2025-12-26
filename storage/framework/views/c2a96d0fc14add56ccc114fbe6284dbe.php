

<?php $__env->startSection('title', 'Review Assignments - REVANA'); ?>
<?php $__env->startSection('page-title', 'Daftar Review Assignment'); ?>

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
    <a href="<?php echo e(route('admin.redemptions.index')); ?>" class="nav-link">
        <i class="bi bi-gift"></i> Reward Redemptions
    </a>
    <a href="<?php echo e(route('admin.points.index')); ?>" class="nav-link">
        <i class="bi bi-coin"></i> Point Management
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row mb-3">
    <div class="col">
        <a href="<?php echo e(route('admin.assignments.create')); ?>" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Assign Reviewer Baru
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
        <i class="bi bi-list-ul"></i> Semua Review Assignments
    </div>
    <div class="card-body">
        <?php if($assignments->isEmpty()): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Belum ada assignment yang dibuat.
            <a href="<?php echo e(route('admin.assignments.create')); ?>">Buat assignment pertama</a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Jurnal</th>
                        <th>Reviewer</th>
                        <th>Status</th>
                        <th>Assigned By</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $assignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($assignment->id); ?></td>
                        <td>
                            <div class="fw-bold"><?php echo e(Str::limit($assignment->journal->title, 50)); ?></div>
                            <small class="text-muted">
                                <span class="badge bg-secondary"><?php echo e($assignment->journal->accreditation); ?></span>
                                <?php echo e($assignment->journal->points); ?> pts
                            </small>
                        </td>
                        <td>
                            <div><?php echo e($assignment->reviewer->name); ?></div>
                            <small class="text-muted"><?php echo e($assignment->reviewer->email); ?></small>
                        </td>
                        <td>
                            <?php if($assignment->status === 'pending'): ?>
                                <span class="badge bg-warning">
                                    <i class="bi bi-clock"></i> Pending
                                </span>
                            <?php elseif($assignment->status === 'accepted'): ?>
                                <span class="badge bg-info">
                                    <i class="bi bi-check"></i> Accepted
                                </span>
                            <?php elseif($assignment->status === 'rejected'): ?>
                                <span class="badge bg-danger">
                                    <i class="bi bi-x-circle"></i> Rejected
                                </span>
                            <?php elseif($assignment->status === 'submitted'): ?>
                                <span class="badge bg-primary">
                                    <i class="bi bi-send"></i> Submitted
                                </span>
                            <?php elseif($assignment->status === 'approved'): ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Approved
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small><?php echo e($assignment->assignedBy->name); ?></small>
                        </td>
                        <td>
                            <small><?php echo e($assignment->created_at->format('d M Y')); ?></small>
                            <br>
                            <small class="text-muted"><?php echo e($assignment->created_at->diffForHumans()); ?></small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="<?php echo e(route('admin.assignments.show', $assignment)); ?>" 
                                   class="btn btn-info" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if($assignment->status === 'pending'): ?>
                                <form action="<?php echo e(route('admin.assignments.destroy', $assignment)); ?>" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('Yakin ingin menghapus assignment ini?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-3">
            <?php echo e($assignments->links()); ?>

        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Stats Summary -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-muted">Total</h5>
                <h2><?php echo e($assignments->total()); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-warning">Pending</h5>
                <h2><?php echo e($assignments->where('status', 'pending')->count()); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-info">Accepted</h5>
                <h2><?php echo e($assignments->where('status', 'accepted')->count()); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="text-success">Completed</h5>
                <h2><?php echo e($assignments->where('status', 'approved')->count()); ?></h2>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\LPKD-APJI\REVANA\resources\views/admin/assignments/index.blade.php ENDPATH**/ ?>