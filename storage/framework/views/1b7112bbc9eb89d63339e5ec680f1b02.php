

<?php $__env->startSection('title', 'My Tasks - REVANA'); ?>
<?php $__env->startSection('page-title', 'Tugas Review Saya'); ?>

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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clipboard-check"></i> Daftar Tugas Review
            </div>
            <div class="card-body">
                <!-- Filter Tabs -->
                <ul class="nav nav-tabs mb-3" id="statusTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" onclick="filterTasks('all')">
                            Semua <span class="badge bg-secondary ms-1"><?php echo e($assignments->total()); ?></span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" onclick="filterTasks('PENDING')">
                            Pending <span class="badge bg-warning ms-1"><?php echo e($assignments->where('status', 'PENDING')->count()); ?></span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" onclick="filterTasks('active')">
                            Active <span class="badge bg-info ms-1"><?php echo e($assignments->whereIn('status', ['ACCEPTED', 'ON_PROGRESS', 'SUBMITTED'])->count()); ?></span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" onclick="filterTasks('APPROVED')">
                            Completed <span class="badge bg-success ms-1"><?php echo e($assignments->where('status', 'APPROVED')->count()); ?></span>
                        </button>
                    </li>
                </ul>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Jurnal</th>
                                <th>Akreditasi</th>
                                <th>Points</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="taskTableBody">
                            <?php $__empty_1 = true; $__currentLoopData = $assignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr data-status="<?php echo e($assignment->status); ?>">
                                <td><?php echo e($loop->iteration + ($assignments->currentPage() - 1) * $assignments->perPage()); ?></td>
                                <td>
                                    <strong><?php echo e(Str::limit($assignment->journal->title, 50)); ?></strong><br>
                                    <small class="text-muted">
                                        <i class="bi bi-link-45deg"></i>
                                        <a href="<?php echo e($assignment->journal->link); ?>" target="_blank">Buka Jurnal</a>
                                    </small>
                                    <?php if($assignment->status === 'APPROVED'): ?>
                                        <br>
                                        <span class="badge bg-success mt-1">
                                            <i class="bi bi-check-circle-fill"></i> Poin Diterima (+<?php echo e($assignment->journal->points); ?> pts)
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-info"><?php echo e($assignment->journal->accreditation); ?></span></td>
                                <td>
                                    <span class="badge bg-<?php echo e($assignment->status === 'APPROVED' ? 'success' : 'warning'); ?>">
                                        <?php echo e($assignment->journal->points); ?> pts
                                    </span>
                                </td>
                                <td>
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
                                    <span class="badge bg-<?php echo e($color); ?>"><?php echo e($assignment->status); ?></span>
                                </td>
                                <td><?php echo e($assignment->created_at->format('d M Y')); ?></td>
                                <td>
                                    <a href="<?php echo e(route('reviewer.tasks.show', $assignment)); ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                    <p class="mt-2">Belum ada tugas</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <?php echo e($assignments->links()); ?>

                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function filterTasks(status) {
    // Update active tab
    document.querySelectorAll('#statusTabs button').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    const rows = document.querySelectorAll('#taskTableBody tr[data-status]');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const rowStatus = row.getAttribute('data-status');
        
        if (status === 'all') {
            row.style.display = '';
            visibleCount++;
        } else if (status === 'active') {
            // Active includes ACCEPTED, ON_PROGRESS, SUBMITTED
            if (['ACCEPTED', 'ON_PROGRESS', 'SUBMITTED'].includes(rowStatus)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        } else {
            if (rowStatus === status) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        }
    });
    
    // Show/hide empty message
    const emptyRow = document.querySelector('#taskTableBody tr:not([data-status])');
    if (emptyRow) {
        emptyRow.style.display = visibleCount === 0 ? '' : 'none';
    }
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\LPKD-APJI\REVANA\resources\views/reviewer/tasks/index.blade.php ENDPATH**/ ?>