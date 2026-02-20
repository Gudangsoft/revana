<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReviewRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Exports\ReviewRequestsExport;
use Maatwebsite\Excel\Facades\Excel;

class ReviewRequestController extends Controller
{
    /**
     * Display a listing of review requests (for admin)
     */
    public function index(Request $request)
    {
        $query = ReviewRequest::with(['reviewer.fieldOfStudy', 'approver'])
            ->orderBy('created_at', 'desc');

        // Filter by status if provided
        if ($request->has('status') && in_array($request->status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $request->status);
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('reviewer', function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('institution', 'like', '%' . $search . '%');
            });
        }

        $reviewRequests = $query->paginate(request()->input('per_page', 20))->withQueryString();

        return view('admin.review-requests.index', compact('reviewRequests'));
    }

    /**
     * Export review requests to Excel
     */
    public function export(Request $request)
    {
        $status = $request->get('status');
        $search = $request->get('search');
        
        $filename = 'review_requests_' . date('Y-m-d_His') . '.xlsx';
        
        return Excel::download(
            new ReviewRequestsExport($status, $search), 
            $filename
        );
    }

    /**
     * Show the form for creating a new review request (for reviewer)
     */
    public function create()
    {
        return view('reviewer.review-requests.create');
    }

    /**
     * Store a newly created review request
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'number_of_journals' => 'required|integer|min:1',
            'number_of_days' => 'required|integer|min:1|max:5',
            'notes' => 'nullable|string|max:1000',
        ], [
            'number_of_journals.required' => 'Jumlah jurnal harus diisi',
            'number_of_journals.integer' => 'Jumlah jurnal harus berupa angka',
            'number_of_journals.min' => 'Jumlah jurnal minimal 1',
            'number_of_days.required' => 'Lama hari harus diisi',
            'number_of_days.integer' => 'Lama hari harus berupa angka',
            'number_of_days.min' => 'Lama hari minimal 1',
            'number_of_days.max' => 'Lama hari maksimal 5 hari',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        ReviewRequest::create([
            'reviewer_id' => Auth::id(),
            'number_of_journals' => $request->number_of_journals,
            'number_of_days' => $request->number_of_days,
            'notes' => $request->notes,
            'status' => 'pending',
        ]);

        return redirect()->route('reviewer.review-requests.my-requests')
            ->with('success', 'Permintaan review berhasil diajukan!');
    }

    /**
     * Display the specified review request
     */
    public function show(ReviewRequest $reviewRequest)
    {
        $reviewRequest->load(['reviewer.fieldOfStudy', 'approver']);
        return view('admin.review-requests.show', compact('reviewRequest'));
    }

    /**
     * Show reviewer's own review requests
     */
    public function myRequests()
    {
        $reviewRequests = ReviewRequest::where('reviewer_id', Auth::id())
            ->with('approver')
            ->orderBy('created_at', 'desc')
            ->paginate(request()->input('per_page', 10));

        return view('reviewer.review-requests.index', compact('reviewRequests'));
    }

    /**
     * Approve a review request (admin only)
     */
    public function approve(Request $request, ReviewRequest $reviewRequest)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $reviewRequest->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
            'admin_notes' => $request->admin_notes,
        ]);

        // Redirect to assignment create page with pre-filled reviewer and journal count
        return redirect()->route('admin.assignments.create', [
            'reviewer_id' => $reviewRequest->reviewer_id,
            'review_request_id' => $reviewRequest->id,
            'journal_count' => $reviewRequest->number_of_journals
        ])->with('success', 'Permintaan review disetujui! Silakan lengkapi form penugasan untuk ' . $reviewRequest->number_of_journals . ' jurnal.');
    }

    /**
     * Reject a review request (admin only)
     */
    public function reject(Request $request, ReviewRequest $reviewRequest)
    {
        $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ], [
            'admin_notes.required' => 'Alasan penolakan harus diisi',
        ]);

        $reviewRequest->update([
            'status' => 'rejected',
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->back()
            ->with('success', 'Permintaan review berhasil ditolak!');
    }
}
