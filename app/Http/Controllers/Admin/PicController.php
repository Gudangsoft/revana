<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pic;
use App\Models\PicPointHistory;
use App\Exports\PicsExport;
use App\Imports\PicsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PicController extends Controller
{
    public function index(Request $request)
    {
        $query = Pic::query();
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('phone', 'like', "%$search%");
            });
        }
        $pics = $query->orderBy('name')->paginate(request()->input('per_page', 20));
        return view('admin.pics.index', compact('pics'));
    }

    public function create()
    {
        return view('admin.pics.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        
        // Hash password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        Pic::create($validated);

        return redirect()->route('admin.pics.index')
            ->with('success', 'PIC berhasil ditambahkan');
    }

    public function edit(Pic $pic)
    {
        return view('admin.pics.edit', compact('pic'));
    }

    public function update(Request $request, Pic $pic)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        
        // Hash password if provided, otherwise remove from update
        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $pic->update($validated);

        return redirect()->route('admin.pics.index')
            ->with('success', 'PIC berhasil diupdate');
    }

    public function destroy(Pic $pic)
    {
        $pic->delete();

        return redirect()->route('admin.pics.index')
            ->with('success', 'PIC berhasil dihapus');
    }

    public function export()
    {
        $filename = 'pics_' . date('Y-m-d_His') . '.xlsx';
        return Excel::download(new PicsExport, $filename);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
        ]);

        try {
            $import = new PicsImport;
            Excel::import($import, $request->file('file'));

            $created = $import->getCreatedCount();
            $updated = $import->getUpdatedCount();
            $skipped = $import->getSkippedCount();
            
            $message = "Import berhasil! {$created} PIC baru ditambahkan, {$updated} PIC diupdate.";
            if ($skipped > 0) {
                $message .= " {$skipped} baris dilewati karena data tidak valid (email tidak valid atau nama kosong).";
            }
            
            return redirect()->route('admin.pics.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.pics.index')
                ->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new class implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
            public function array(): array
            {
                return [
                    ['John Doe', 'john_doe', 'john@example.com', '081234567890', 'Aktif'],
                    ['Jane Smith', 'jane_smith', 'jane@example.com', '089876543210', 'Nonaktif'],
                ];
            }
            
            public function headings(): array
            {
                return ['Nama', 'Username', 'Email', 'Telepon', 'Status'];
            }
        }, 'template_pics.xlsx');
    }

    /**
     * Login as a PIC (Admin impersonation)
     */
    public function loginAs(Pic $pic)
    {
        if (!$pic->is_active) {
            return redirect()->route('admin.pics.index')
                ->with('error', 'PIC tidak aktif, tidak dapat login sebagai PIC ini.');
        }

        // Store original admin user ID in session for potential return
        session(['admin_impersonating' => Auth::id()]);
        
        // Logout from admin (web guard) and login as PIC (pic guard)
        Auth::guard('pic')->login($pic);
        
        return redirect()->route('pic.dashboard')
            ->with('success', 'Anda sekarang login sebagai ' . $pic->name);
    }

    /**
     * Show PIC activity report
     */
    public function activityReport(Request $request)
    {
        // Get all PICs
        $query = Pic::query();
        
        // Filter by PIC
        if ($request->filled('pic_id')) {
            $query->where('id', $request->pic_id);
        }
        
        // Only show active PICs by default
        if (!$request->filled('show_inactive')) {
            $query->where('is_active', true);
        }
        
        $pics = $query->orderBy('total_points', 'desc')->get();
        
        // Calculate filtered points for each PIC
        $pics->each(function($pic) use ($request) {
            $pointQuery = PicPointHistory::where('pic_id', $pic->id);
            
            if ($request->filled('tanggal_dari')) {
                $pointQuery->whereDate('created_at', '>=', $request->tanggal_dari);
            }
            if ($request->filled('tanggal_sampai')) {
                $pointQuery->whereDate('created_at', '<=', $request->tanggal_sampai);
            }
            
            $pic->filtered_points = $pointQuery->sum('points_earned');
            $pic->filtered_tasks = $pointQuery->count();
            
            // Get breakdown by step
            $pic->step_breakdown = PicPointHistory::where('pic_id', $pic->id)
                ->when($request->filled('tanggal_dari'), function($q) use ($request) {
                    $q->whereDate('created_at', '>=', $request->tanggal_dari);
                })
                ->when($request->filled('tanggal_sampai'), function($q) use ($request) {
                    $q->whereDate('created_at', '<=', $request->tanggal_sampai);
                })
                ->select('step', DB::raw('COUNT(*) as count'), DB::raw('SUM(points_earned) as total'))
                ->groupBy('step')
                ->get();
        });
        
        // Overall statistics
        $stats = [
            'total_pics' => $pics->count(),
            'active_pics' => Pic::where('is_active', true)->count(),
            'total_points_given' => $pics->sum('filtered_points'),
            'total_tasks_completed' => $pics->sum('filtered_tasks'),
        ];
        
        // Get all PICs for filter dropdown
        $allPics = Pic::orderBy('name')->get();
        
        return view('admin.pics.activity-report', compact('pics', 'stats', 'allPics'));
    }

    /**
     * Reset individual PIC password to default
     */
    public function resetPassword(Pic $pic)
    {
        $defaultPassword = 'pic123';
        $pic->password = bcrypt($defaultPassword);
        $pic->save();

        return redirect()->route('admin.pics.index')
            ->with('success', "Password untuk {$pic->name} telah direset ke default: {$defaultPassword}");
    }

    /**
     * Reset all PICs password to default
     */
    public function resetAllPasswords()
    {
        $defaultPassword = bcrypt('pic123');
        
        // Use single query for better performance
        $count = DB::table('pics')->update([
            'password' => $defaultPassword,
            'updated_at' => now()
        ]);
        
        return redirect()->route('admin.pics.index')
            ->with('success', "Berhasil! Password {$count} PIC telah direset ke default: pic123");
    }
}
