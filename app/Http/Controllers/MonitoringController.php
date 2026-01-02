<?php

namespace App\Http\Controllers;

use App\Models\ReviewAssignment;
use App\Models\ReviewResult;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    public function index()
    {
        // Get statistics
        $stats = [
            'total_assignments' => ReviewAssignment::count(),
            'pending_reviews' => ReviewAssignment::where('status', 'PENDING')->count(),
            'in_progress_reviews' => ReviewAssignment::whereIn('status', ['ACCEPTED', 'ON_PROGRESS'])->count(),
            'completed_reviews' => ReviewAssignment::where('status', 'APPROVED')->count(),
            'revision_reviews' => ReviewAssignment::where('status', 'REVISION')->count(),
        ];

        // Get review assignments
        $assignments = ReviewAssignment::with(['reviewer', 'assignedBy', 'result'])
            ->latest()
            ->paginate(20);

        return view('monitoring.index', compact('stats', 'assignments'));
    }

    public function show(ReviewAssignment $assignment)
    {
        $assignment->load(['reviewer', 'assignedBy', 'result']);
        return view('monitoring.show', compact('assignment'));
    }
}
    {
        $journal->load(['creator', 'assignments.reviewer', 'assignments.result']);
        
        return view('monitoring.show', compact('journal'));
    }
}
