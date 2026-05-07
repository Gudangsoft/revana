<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::latest();

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }
        if ($request->filled('causer')) {
            $query->where('causer_name', 'like', '%' . $request->causer . '%');
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(50)->withQueryString();

        $events = ActivityLog::select('event')->distinct()->pluck('event');

        return view('admin.activity-logs.index', compact('logs', 'events'));
    }
}
