<?php

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use App\Models\DeadlineExtensionRequest;
use App\Models\ReviewAssignment;
use Illuminate\Http\Request;

class DeadlineExtensionController extends Controller
{
    public function store(Request $request, ReviewAssignment $assignment)
    {
        $isReviewer = in_array(auth()->id(), $assignment->assignedReviewerIds());
        if (!$isReviewer) {
            abort(403);
        }

        // Satu reviewer cuma boleh mengajukan SATU KALI per assignment — dicek dari
        // status manapun (PENDING/APPROVED/REJECTED), bukan cuma yang masih pending.
        if ($assignment->extensionRequestFor(auth()->id())) {
            return back()->with('error', 'Anda sudah pernah mengajukan permintaan perpanjangan waktu untuk tugas ini.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
            'requested_deadline' => 'nullable|date|after:today',
        ]);

        DeadlineExtensionRequest::create([
            'review_assignment_id' => $assignment->id,
            'reviewer_id' => auth()->id(),
            'reason' => $validated['reason'],
            'requested_deadline' => $validated['requested_deadline'] ?? null,
            'status' => 'PENDING',
        ]);

        return back()->with('success', 'Permintaan perpanjangan waktu berhasil diajukan. Menunggu persetujuan admin.');
    }
}
