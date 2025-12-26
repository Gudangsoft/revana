

<?php $__env->startSection('title', 'Kelola Jurnal - REVANA'); ?>
<?php $__env->startSection('page-title', 'Kelola Jurnal'); ?>

<?php $__env->startSection('sidebar'); ?>
    <a href="<?php echo e(route('admin.dashboard')); ?>" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="<?php echo e(route('admin.journals.index')); ?>" class="nav-link active">
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
    <a href="<?php echo e(route('admin.marketings.index')); ?>" class="nav-link">
        <i class="bi bi-megaphone"></i> Marketing
    </a>
    <a href="<?php echo e(route('admin.pics.index')); ?>" class="nav-link">
        <i class="bi bi-person-badge"></i> PIC
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-journal-text"></i> Daftar Jurnal</span>
                <a href="<?php echo e(route('admin.journals.create')); ?>" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Tambah Jurnal
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Judul</th>
                                <th>Akreditasi</th>
                                <th>Points</th>
                                <th class="hide-mobile">Terbitan</th>
                                <th class="hide-mobile">Marketing</th>
                                <th class="hide-mobile">PIC</th>
                                <th>Dibuat Oleh</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $journals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $journal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($loop->iteration + ($journals->currentPage() - 1) * $journals->perPage()); ?></td>
                                <td>
                                    <strong><?php echo e(Str::limit($journal->title, 60)); ?></strong><br>
                                    <small class="text-muted">
                                        <i class="bi bi-link-45deg"></i>
                                        <a href="<?php echo e($journal->link); ?>" target="_blank">Lihat Jurnal</a>
                                    </small>
                                </td>
                                <td><span class="badge bg-info"><?php echo e($journal->accreditation); ?></span></td>
                                <td><span class="badge bg-success"><?php echo e($journal->points); ?> pts</span></td>
                                <td class="hide-mobile">
                                    <?php if($journal->publisher): ?>
                                        <small><?php echo e(Str::limit($journal->publisher, 25)); ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">-</small>
                                    <?php endif; ?>
                                </td>
                                <td class="hide-mobile">
                                    <?php if($journal->marketing): ?>
                                        <small><?php echo e(Str::limit($journal->marketing, 25)); ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">-</small>
                                    <?php endif; ?>
                                </td>
                                <td class="hide-mobile">
                                    <?php if($journal->pic): ?>
                                        <small><?php echo e(Str::limit($journal->pic, 25)); ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">-</small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($journal->creator->name); ?></td>
                                <td><?php echo e($journal->created_at->format('d M Y')); ?></td>
                                <td>
                                    <a href="<?php echo e(route('admin.journals.edit', $journal)); ?>" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="<?php echo e(route('admin.journals.destroy', $journal)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted">Belum ada data jurnal</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <?php echo e($journals->links()); ?>

                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\LPKD-APJI\REVANA\resources\views/admin/journals/index.blade.php ENDPATH**/ ?>