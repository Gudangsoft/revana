<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Pic;

class Submission extends Model
{
    use HasFactory;

    // Process Type Constants
    const PROCESS_NORMAL = 'normal';
    const PROCESS_FASTTRACK = 'fasttrack';

    protected $fillable = [
        // Data Submit
        'kode_submit',
        'kode_loa',
        'journal_slot_id',
        'kategori_id',
        'jenis_jurnal_id',
        'marketing_id',
        'id_artikel',
        'judul_artikel',
        'link_artikel',
        'file_artikel',
        'file_artikel_original_name',
        'nama_penulis',
        'no_hp_penulis',
        'username_author',
        'password_author',
        'pic_marketing',
        'petugas_submit_id',
        
        // Proses Workflow - Editor 1
        'petugas_editor1_id',
        'username_editor',
        'password_editor',
        'editor1_valid',
        'editor1_validated_at',
        
        // Author 1
        'petugas_author1_id',
        'author1_valid',
        'author1_validated_at',
        
        // Editor 2
        'petugas_editor2_id',
        'editor2_valid',
        'editor2_validated_at',
        
        // Reviewer 1
        'petugas_reviewer1_id',
        'username_reviewer1',
        'password_reviewer1',
        'catatan_reviewer1',
        'reviewer1_valid',
        'reviewer1_validated_at',
        
        // Reviewer 2
        'petugas_reviewer2_id',
        'username_reviewer2',
        'password_reviewer2',
        'catatan_reviewer2',
        'reviewer2_valid',
        'reviewer2_validated_at',
        
        // Editor 3
        'petugas_editor3_id',
        'editor3_valid',
        'editor3_validated_at',
        
        // Author 2
        'petugas_author2_id',
        'author2_valid',
        'author2_validated_at',
        
        // Production
        'petugas_production_id',
        'production_valid',
        'production_validated_at',
        
        // Hasil
        'link_publish',
        'status',
        'process_type',
        'tanggal_submit',
        'notes',
        'catatan_marketing',
        'created_by',
        'edit_count',
    ];

    protected $casts = [
        'editor1_valid' => 'boolean',
        'author1_valid' => 'boolean',
        'editor2_valid' => 'boolean',
        'reviewer1_valid' => 'boolean',
        'reviewer2_valid' => 'boolean',
        'editor3_valid' => 'boolean',
        'author2_valid' => 'boolean',
        'production_valid' => 'boolean',
        'editor1_validated_at' => 'datetime',
        'author1_validated_at' => 'datetime',
        'editor2_validated_at' => 'datetime',
        'reviewer1_validated_at' => 'datetime',
        'reviewer2_validated_at' => 'datetime',
        'editor3_validated_at' => 'datetime',
        'author2_validated_at' => 'datetime',
        'production_validated_at' => 'datetime',
        'tanggal_submit' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($submission) {
            // Auto generate kode_submit if not set
            if (empty($submission->kode_submit)) {
                $submission->kode_submit = self::generateKodeSubmit();
            }
            
            // Auto generate kode_loa: kode_submit + SIPERA
            if (empty($submission->kode_loa)) {
                $submission->kode_loa = $submission->kode_submit . 'SIPERA';
            }
            
            // Set tanggal_submit to today if not set
            if (empty($submission->tanggal_submit)) {
                $submission->tanggal_submit = now()->toDateString();
            }
        });

        static::created(function ($submission) {
            // Increment slot_terpakai on journal_slot
            $slot = $submission->journalSlot;
            if ($slot) {
                $slot->increment('slot_terpakai');
            }
        });

        static::deleted(function ($submission) {
            // Decrement slot_terpakai on journal_slot
            $slot = $submission->journalSlot;
            if ($slot && $slot->slot_terpakai > 0) {
                $slot->decrement('slot_terpakai');
            }
        });
    }

    public static function generateKodeSubmit()
    {
        $prefix = 'SUB';
        $year = date('Y');
        $month = date('m');
        $lastSubmission = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();
        
        $number = $lastSubmission ? (int) substr($lastSubmission->kode_submit, -4) + 1 : 1;
        
        return $prefix . $year . $month . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    // Relationships
    public function journalSlot()
    {
        return $this->belongsTo(JournalSlot::class, 'journal_slot_id');
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function jenisJurnal()
    {
        return $this->belongsTo(JenisJurnal::class, 'jenis_jurnal_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function marketing()
    {
        return $this->belongsTo(Marketing::class, 'marketing_id');
    }

    public function marketingPointHistory()
    {
        return $this->hasMany(MarketingPointHistory::class, 'submission_id');
    }

    public function petugasSubmit()
    {
        return $this->belongsTo(Pic::class, 'petugas_submit_id');
    }

    public function petugasEditor1()
    {
        return $this->belongsTo(Pic::class, 'petugas_editor1_id');
    }

    public function petugasAuthor1()
    {
        return $this->belongsTo(Pic::class, 'petugas_author1_id');
    }

    public function petugasEditor2()
    {
        return $this->belongsTo(Pic::class, 'petugas_editor2_id');
    }

    public function petugasReviewer1()
    {
        return $this->belongsTo(Pic::class, 'petugas_reviewer1_id');
    }

    public function petugasReviewer2()
    {
        return $this->belongsTo(Pic::class, 'petugas_reviewer2_id');
    }

    public function petugasEditor3()
    {
        return $this->belongsTo(Pic::class, 'petugas_editor3_id');
    }

    public function petugasAuthor2()
    {
        return $this->belongsTo(Pic::class, 'petugas_author2_id');
    }

    public function petugasProduction()
    {
        return $this->belongsTo(Pic::class, 'petugas_production_id');
    }

    // Histories relationship
    public function histories()
    {
        return $this->hasMany(SubmissionHistory::class)->orderBy('created_at', 'desc');
    }

    // Get histories for specific step
    public function getStepHistories($step)
    {
        return $this->histories()->where('step', $step)->orderBy('created_at', 'asc')->get();
    }

    // Count revisions for specific step
    public function getRevisionCount($step)
    {
        return $this->histories()
            ->where('step', $step)
            ->where('action', 'revision_request')
            ->count();
    }

    // Log history
    public function logHistory($step, $action, $notes = null, $data = null, $userId = null)
    {
        $revisionNumber = 0;
        
        // Calculate revision number if action is revision_request
        if ($action === 'revision_request') {
            $revisionNumber = $this->getRevisionCount($step) + 1;
        } elseif ($action === 'revision_submit') {
            $revisionNumber = $this->getRevisionCount($step);
        }

        return $this->histories()->create([
            'step' => $step,
            'action' => $action,
            'user_id' => $userId ?? auth()->id(),
            'notes' => $notes,
            'data' => $data,
            'revision_number' => $revisionNumber,
        ]);
    }

    // Status helpers
    public static function getStatusOptions()
    {
        return [
            'SUBMITTED' => 'Submitted',
            'EDITOR1_PROCESS' => 'Editor 1 Process',
            'AUTHOR1_PROCESS' => 'Author 1 Process',
            'EDITOR2_PROCESS' => 'Editor 2 Process',
            'REVIEWER1_PROCESS' => 'Reviewer 1 Process',
            'REVIEWER2_PROCESS' => 'Reviewer 2 Process',
            'EDITOR3_PROCESS' => 'Editor 3 Process',
            'AUTHOR2_PROCESS' => 'Author 2 Process',
            'PRODUCTION_PROCESS' => 'Production Process',
            'PUBLISHED' => 'Published',
            'REJECTED' => 'Rejected',
        ];
    }

    public function getStatusLabelAttribute()
    {
        // Determine real status from validation flags
        $realStatus = $this->getRealStatus();
        $statuses = self::getStatusOptions();
        return $statuses[$realStatus] ?? $realStatus;
    }

    public function getStatusBadgeClassAttribute()
    {
        $realStatus = $this->getRealStatus();
        return match($realStatus) {
            'SUBMITTED' => 'bg-secondary',
            'EDITOR1_PROCESS' => 'bg-info',
            'AUTHOR1_PROCESS' => 'bg-info',
            'EDITOR2_PROCESS' => 'bg-info',
            'REVIEWER1_PROCESS' => 'bg-warning',
            'REVIEWER2_PROCESS' => 'bg-warning',
            'EDITOR3_PROCESS' => 'bg-info',
            'AUTHOR2_PROCESS' => 'bg-info',
            'PRODUCTION_PROCESS' => 'bg-primary',
            'PUBLISHED' => 'bg-success',
            'REJECTED' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    // Get current step number (1-10) based on real progress
    public function getCurrentStepAttribute()
    {
        $realStatus = $this->getRealStatus();
        return match($realStatus) {
            'SUBMITTED' => 1,
            'EDITOR1_PROCESS' => 2,
            'AUTHOR1_PROCESS' => 3,
            'EDITOR2_PROCESS' => 4,
            'REVIEWER1_PROCESS' => 5,
            'REVIEWER2_PROCESS' => 6,
            'EDITOR3_PROCESS' => 7,
            'AUTHOR2_PROCESS' => 8,
            'PRODUCTION_PROCESS' => 9,
            'PUBLISHED' => 10,
            'REJECTED' => 0,
            default => 1,
        };
    }

    // Calculate progress percentage
    public function getProgressPercentageAttribute()
    {
        $realStatus = $this->getRealStatus();
        if ($realStatus === 'REJECTED') return 0;
        if ($realStatus === 'PUBLISHED') return 100;
        
        return ($this->current_step / 10) * 100;
    }

    /**
     * Determine real status from actual validation flags,
     * because the status field may not always be updated correctly.
     */
    public function getRealStatus()
    {
        if ($this->status === 'REJECTED') return 'REJECTED';
        
        // If link_publish exists, it's published
        if (!empty($this->link_publish)) {
            return 'PUBLISHED';
        }
        
        // Check production_valid
        if ($this->production_valid) {
            return 'PUBLISHED';
        }
        
        // Check from the latest validated step backwards
        if ($this->author2_valid) {
            return 'PRODUCTION_PROCESS';
        }
        if ($this->editor3_valid) {
            return 'AUTHOR2_PROCESS';
        }
        if ($this->reviewer2_valid) {
            return 'EDITOR3_PROCESS';
        }
        if ($this->reviewer1_valid) {
            return 'REVIEWER2_PROCESS';
        }
        if ($this->editor2_valid) {
            return 'REVIEWER1_PROCESS';
        }
        if ($this->author1_valid) {
            return 'EDITOR2_PROCESS';
        }
        if ($this->editor1_valid) {
            return 'AUTHOR1_PROCESS';
        }
        
        // If petugas_editor1 is assigned but not validated, it's in editor1 process
        if ($this->petugas_editor1_id) {
            return 'EDITOR1_PROCESS';
        }
        
        // Fall back to database status
        return $this->status ?? 'SUBMITTED';
    }

    // Validate step methods
    public function validateEditor1()
    {
        $this->update([
            'editor1_valid' => true,
            'editor1_validated_at' => now(),
            'status' => 'AUTHOR1_PROCESS',
        ]);
    }

    public function validateAuthor1()
    {
        $this->update([
            'author1_valid' => true,
            'author1_validated_at' => now(),
            'status' => 'EDITOR2_PROCESS',
        ]);
    }

    public function validateEditor2()
    {
        $this->update([
            'editor2_valid' => true,
            'editor2_validated_at' => now(),
            'status' => 'REVIEWER1_PROCESS',
        ]);
    }

    public function validateReviewer1()
    {
        $this->update([
            'reviewer1_valid' => true,
            'reviewer1_validated_at' => now(),
            'status' => 'REVIEWER2_PROCESS',
        ]);
    }

    public function validateReviewer2()
    {
        $this->update([
            'reviewer2_valid' => true,
            'reviewer2_validated_at' => now(),
            'status' => 'EDITOR3_PROCESS',
        ]);
    }

    public function validateEditor3()
    {
        $this->update([
            'editor3_valid' => true,
            'editor3_validated_at' => now(),
            'status' => 'AUTHOR2_PROCESS',
        ]);
    }

    public function validateAuthor2()
    {
        $this->update([
            'author2_valid' => true,
            'author2_validated_at' => now(),
            'status' => 'PRODUCTION_PROCESS',
        ]);
    }

    public function validateProduction()
    {
        $this->update([
            'production_valid' => true,
            'production_validated_at' => now(),
            'status' => 'PUBLISHED',
        ]);
    }

    /**
     * Recalculate status based on current validation flags.
     * Call this after toggling any validation field to keep status in sync.
     */
    public function recalculateStatus()
    {
        if ($this->status === 'REJECTED') {
            return; // Don't change rejected status
        }

        if ($this->production_valid) {
            $this->status = 'PUBLISHED';
        } elseif ($this->author2_valid) {
            $this->status = 'PRODUCTION_PROCESS';
        } elseif ($this->editor3_valid) {
            // If author2 is not assigned, skip to production
            if (!$this->petugas_author2_id) {
                $this->status = 'PRODUCTION_PROCESS';
            } else {
                $this->status = 'AUTHOR2_PROCESS';
            }
        } elseif ($this->reviewer1_valid && $this->reviewer2_valid) {
            // Both reviewers done
            if (!$this->petugas_editor3_id) {
                // Editor3 not assigned, skip
                if (!$this->petugas_author2_id) {
                    $this->status = 'PRODUCTION_PROCESS';
                } else {
                    $this->status = 'AUTHOR2_PROCESS';
                }
            } else {
                $this->status = 'EDITOR3_PROCESS';
            }
        } elseif ($this->editor2_valid) {
            // In reviewer stage - determine which reviewer is being processed
            if ($this->reviewer1_valid && !$this->reviewer2_valid) {
                $this->status = 'REVIEWER2_PROCESS';
            } elseif (!$this->reviewer1_valid && $this->reviewer2_valid) {
                $this->status = 'REVIEWER1_PROCESS';
            } else {
                $this->status = 'REVIEWER1_PROCESS';
            }
        } elseif ($this->author1_valid) {
            $this->status = 'EDITOR2_PROCESS';
        } elseif ($this->editor1_valid) {
            $this->status = 'AUTHOR1_PROCESS';
        } else {
            $this->status = 'SUBMITTED';
        }
    }

    // Process Type Helpers
    public function isNormal()
    {
        return $this->process_type === self::PROCESS_NORMAL || $this->process_type === null;
    }

    public function isFasttrack()
    {
        return $this->process_type === self::PROCESS_FASTTRACK;
    }

    public static function getProcessTypeOptions()
    {
        return [
            self::PROCESS_NORMAL => 'Normal',
            self::PROCESS_FASTTRACK => 'Fasttrack',
        ];
    }

    public function getProcessTypeLabelAttribute()
    {
        $types = self::getProcessTypeOptions();
        return $types[$this->process_type] ?? 'Normal';
    }

    public function getProcessTypeBadgeClassAttribute()
    {
        return match($this->process_type) {
            self::PROCESS_FASTTRACK => 'bg-warning text-dark',
            default => 'bg-secondary',
        };
    }
}
