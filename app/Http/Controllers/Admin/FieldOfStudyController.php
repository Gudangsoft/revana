<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FieldOfStudy;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\FieldOfStudyImport;

class FieldOfStudyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fields = FieldOfStudy::withCount(['users', 'reviewerRegistrations'])
            ->ordered()
            ->paginate(15);
        
        return view('admin.field-of-studies.index', compact('fields'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.field-of-studies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:field_of_studies,name',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'order' => 'nullable|integer|min:0'
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['order'] = $validated['order'] ?? 0;

        FieldOfStudy::create($validated);

        return redirect()
            ->route('admin.field-of-studies.index')
            ->with('success', 'Bidang ilmu berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FieldOfStudy $fieldOfStudy)
    {
        return view('admin.field-of-studies.edit', compact('fieldOfStudy'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FieldOfStudy $fieldOfStudy)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:field_of_studies,name,' . $fieldOfStudy->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'order' => 'nullable|integer|min:0'
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['order'] = $validated['order'] ?? 0;

        $fieldOfStudy->update($validated);

        return redirect()
            ->route('admin.field-of-studies.index')
            ->with('success', 'Bidang ilmu berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FieldOfStudy $fieldOfStudy)
    {
        // Check if field is being used
        $usageCount = $fieldOfStudy->users()->count() + $fieldOfStudy->reviewerRegistrations()->count();
        
        if ($usageCount > 0) {
            return redirect()
                ->route('admin.field-of-studies.index')
                ->with('error', "Bidang ilmu tidak dapat dihapus karena sedang digunakan oleh {$usageCount} data.");
        }

        $fieldOfStudy->delete();

        return redirect()
            ->route('admin.field-of-studies.index')
            ->with('success', 'Bidang ilmu berhasil dihapus.');
    }

    /**
     * Toggle active status
     */
    public function toggleStatus(FieldOfStudy $fieldOfStudy)
    {
        $fieldOfStudy->update([
            'is_active' => !$fieldOfStudy->is_active
        ]);

        $status = $fieldOfStudy->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('admin.field-of-studies.index')
            ->with('success', "Bidang ilmu berhasil {$status}.");
    }

    /**
     * Import from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ], [
            'file.required' => 'File Excel wajib dipilih',
            'file.mimes' => 'File harus berformat Excel (xlsx, xls, atau csv)',
            'file.max' => 'Ukuran file maksimal 2MB'
        ]);

        try {
            Excel::import(new FieldOfStudyImport, $request->file('file'));

            return redirect()
                ->route('admin.field-of-studies.index')
                ->with('success', 'Data bidang ilmu berhasil diimport dari Excel.');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];
            
            foreach ($failures as $failure) {
                $errorMessages[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            }

            return redirect()
                ->route('admin.field-of-studies.index')
                ->with('error', 'Import gagal: ' . implode(' | ', $errorMessages));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.field-of-studies.index')
                ->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    /**
     * Download template Excel
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_bidang_ilmu.csv"',
        ];

        $columns = ['name', 'description', 'order', 'is_active'];
        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, $columns);
            
            // Sample data
            fputcsv($file, ['Engineering', 'Teknik dan Rekayasa', 1, 1]);
            fputcsv($file, ['Science', 'Ilmu Pengetahuan Alam', 2, 1]);
            fputcsv($file, ['Health', 'Kesehatan', 3, 1]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
