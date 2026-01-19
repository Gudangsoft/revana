<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pic;
use App\Exports\PicsExport;
use App\Imports\PicsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class PicController extends Controller
{
    public function index()
    {
        $pics = Pic::latest()->paginate(20);
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
            
            return redirect()->route('admin.pics.index')
                ->with('success', "Import berhasil! {$created} PIC baru ditambahkan, {$updated} PIC diupdate.");
        } catch (\Exception $e) {
            return redirect()->route('admin.pics.index')
                ->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $headers = ['Nama', 'Username', 'Email', 'Telepon', 'Status'];
        $sample = [
            ['John Doe', 'john_doe', 'john@example.com', '081234567890', 'Aktif'],
            ['Jane Smith', 'jane_smith', 'jane@example.com', '089876543210', 'Nonaktif'],
        ];

        $callback = function() use ($headers, $sample) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            foreach ($sample as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_pics.csv"',
        ]);
    }
}
