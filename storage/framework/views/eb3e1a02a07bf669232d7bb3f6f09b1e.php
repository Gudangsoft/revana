

<?php $__env->startSection('title', 'Assign Reviewer - REVANA'); ?>
<?php $__env->startSection('page-title', 'Tugaskan Reviewer'); ?>

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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-person-plus"></i> Form Assign Reviewer
            </div>
            <div class="card-body">
                <form action="<?php echo e(route('admin.assignments.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>

                    <div class="mb-3">
                        <label class="form-label">Pilih Jurnal <span class="text-danger">*</span></label>
                        <select class="form-select <?php $__errorArgs = ['journal_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                name="journal_id" id="journalSelect" required>
                            <option value="">-- Pilih Jurnal --</option>
                            <?php $__currentLoopData = $journals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $journal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($journal->id); ?>" 
                                    data-title="<?php echo e($journal->title); ?>"
                                    data-accreditation="<?php echo e($journal->accreditation); ?>"
                                    data-points="<?php echo e($journal->points); ?>"
                                    <?php echo e(old('journal_id') == $journal->id ? 'selected' : ''); ?>>
                                <?php echo e(Str::limit($journal->title, 80)); ?> - <?php echo e($journal->accreditation); ?>

                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['journal_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div id="journalInfo" class="alert alert-info" style="display: none;">
                        <strong>Jurnal:</strong> <span id="infoTitle"></span><br>
                        <strong>Akreditasi:</strong> <span id="infoAccreditation"></span><br>
                        <strong>Points Reward:</strong> <span id="infoPoints"></span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pilih Reviewer <span class="text-danger">*</span></label>
                        <select class="form-select <?php $__errorArgs = ['reviewer_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                name="reviewer_id" required>
                            <option value="">-- Pilih Reviewer --</option>
                            <?php $__currentLoopData = $reviewers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reviewer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($reviewer->id); ?>" <?php echo e(old('reviewer_id') == $reviewer->id ? 'selected' : ''); ?>>
                                <?php echo e($reviewer->name); ?> - <?php echo e($reviewer->email); ?>

                                (<?php echo e($reviewer->completed_reviews); ?> reviews, <?php echo e($reviewer->total_points); ?> pts)
                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['reviewer_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Assign Reviewer
                        </button>
                        <a href="<?php echo e(route('admin.assignments.index')); ?>" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <i class="bi bi-info-circle"></i> Informasi
            </div>
            <div class="card-body">
                <h6>Tips Assign Reviewer:</h6>
                <ul class="small">
                    <li>Pilih reviewer yang sesuai dengan bidang jurnal</li>
                    <li>Perhatikan beban kerja reviewer saat ini</li>
                    <li>Reviewer akan menerima notifikasi tugas baru</li>
                    <li>Reviewer bisa menerima atau menolak tugas</li>
                </ul>
                <hr>
                <p class="mb-0 small text-muted">
                    Setelah reviewer menyelesaikan dan hasil review disetujui, 
                    reviewer akan mendapatkan point sesuai akreditasi jurnal.
                </p>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-success text-white">
                <i class="bi bi-people"></i> Statistik Reviewer
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Total Reviewer:</strong> <?php echo e($reviewers->count()); ?></p>
                <p class="mb-0"><strong>Jurnal Tersedia:</strong> <?php echo e($journals->count()); ?></p>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.getElementById('journalSelect').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const infoDiv = document.getElementById('journalInfo');
    
    if (this.value) {
        document.getElementById('infoTitle').textContent = selectedOption.dataset.title;
        document.getElementById('infoAccreditation').textContent = selectedOption.dataset.accreditation;
        document.getElementById('infoPoints').textContent = selectedOption.dataset.points + ' Points';
        infoDiv.style.display = 'block';
    } else {
        infoDiv.style.display = 'none';
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\LPKD-APJI\REVANA\resources\views/admin/assignments/create.blade.php ENDPATH**/ ?>