<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeadlineExtensionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DeadlineExtensionController extends Controller
{
    public function index()
    {
        $requests = DeadlineExtensionRequest::with(['reviewAssignment', 'reviewer', 'respondedBy'])
            ->orderByRaw("FIELD(status, 'PENDING', 'APPROVED', 'REJECTED')")
            ->latest()
            ->paginate(20);

        return view('admin.extension-requests.index', compact('requests'));
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
