<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeadlineExtensionRequest;
use App\Models\ReviewAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DeadlineExtensionController extends Controller
{
    public function index()
    {
        $requests = DeadlineExtensionRequest::with(['reviewAssignment', 'reviewer', 'respondedBy'])
            ->orderByRaw("FIELD(status, 'PENDING', 'APPROVED', 'REJECTED')")
            ->latest()
            ->paginate(20, ['*'], 'requests_page');

        $expiredReviewers = $this->buildExpiredReviewerRows();

        return view('admin.extension-requests.index', compact('requests', 'expiredReviewers'));
    }

    /**
     * Semua reviewer (dari assignment manapun yang deadline-nya sudah lewat & belum
     * selesai/APPROVED-REJECTED) — baik yang sudah mengajukan request maupun yang belum
     * — supaya admin bisa lihat gambaran lengkap, bukan cuma yang sudah request.
     */
    private function buildExpiredReviewerRows()
    {
        $assignments = ReviewAssignment::whereNotIn('status', ['APPROVED', 'REJECTED'])
            ->whereNotNull('deadline')
            ->where('deadline', '<', now()->startOfDay())
            ->with(['reviewer', 'reviewer2', 'reviewer3', 'reviewer4', 'reviewer5', 'extensionRequests'])
            ->orderBy('deadline')
            ->get();

        $rows = collect();
        foreach ($assignments as $assignment) {
            foreach ($assignment->assignedReviewerIds() as $reviewerId) {
                $reviewerUser = collect([
                    $assignment->reviewer, $assignment->reviewer2, $assignment->reviewer3,
                    $assignment->reviewer4, $assignment->reviewer5,
                ])->firstWhere('id', $reviewerId);

                $rows->push([
                    'assignment' => $assignment,
                    'reviewer' => $reviewerUser,
                    'extensionRequest' => $assignment->extensionRequestFor($reviewerId),
                ]);
            }
        }

        return $rows->sortBy('assignment.deadline')->values();
    }

    public function approve(Request $request, DeadlineExtensionRequest $extensionRequest)
    {
        if ($extensionRequest->status !== 'PENDING') {
            return back()->with('error', 'Permintaan ini sudah pernah diproses.');
        }

        $validated = $request->validate([
            'new_deadline' => 'required|date',
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $extensionRequest->update([
            'status' => 'APPROVED',
            'admin_note' => $validated['admin_note'] ?? null,
            'responded_by' => auth()->id(),
            'responded_at' => now(),
        ]);

        $extensionRequest->reviewAssignment->update(['deadline' => $validated['new_deadline']]);
        $this->clearPendingCount();

        return back()->with('success', 'Permintaan perpanjangan disetujui, deadline diperbarui.');
    }

    public function reject(Request $request, DeadlineExtensionRequest $extensionRequest)
    {
        if ($extensionRequest->status !== 'PENDING') {
            return back()->with('error', 'Permintaan ini sudah pernah diproses.');
        }

        $validated = $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $extensionRequest->update([
            'status' => 'REJECTED',
            'admin_note' => $validated['admin_note'] ?? null,
            'responded_by' => auth()->id(),
            'responded_at' => now(),
        ]);
        $this->clearPendingCount();

        return back()->with('success', 'Permintaan perpanjangan ditolak.');
    }

    private function clearPendingCount(): void
    {
        $tenantKey = app()->bound('tenant') ? app('tenant')->subdomain : 'master';
        Cache::forget('admin.pending_extension_requests.' . $tenantKey);
    }
}
