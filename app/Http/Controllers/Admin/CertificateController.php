<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    public function index()
    {
        $certificates = Certificate::latest()->paginate(10);
        return view('admin.certificates.index', compact('certificates'));
    }

    public function create()
    {
        return view('admin.certificates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'certificate_file' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $filePath = $request->file('certificate_file')->store('certificates', 'public');

        Certificate::create([
            'name' => $request->name,
            'description' => $request->description,
            'file_path' => $filePath,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('admin.certificates.index')
            ->with('success', 'Sertifikat berhasil ditambahkan');
    }

    public function show(Certificate $certificate)
    {
        return view('admin.certificates.show', compact('certificate'));
    }

    public function edit(Certificate $certificate)
    {
        return view('admin.certificates.edit', compact('certificate'));
    }

    public function update(Request $request, Certificate $certificate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'certificate_file' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        if ($request->hasFile('certificate_file')) {
            // Delete old file
            if ($certificate->file_path && Storage::disk('public')->exists($certificate->file_path)) {
                Storage::disk('public')->delete($certificate->file_path);
            }
            
            $filePath = $request->file('certificate_file')->store('certificates', 'public');
            $certificate->file_path = $filePath;
        }

        $certificate->name = $request->name;
        $certificate->description = $request->description;
        $certificate->is_active = $request->has('is_active') ? true : false;
        $certificate->save();

        return redirect()->route('admin.certificates.index')
            ->with('success', 'Sertifikat berhasil diupdate');
    }

    public function destroy(Certificate $certificate)
    {
        // Delete file
        if ($certificate->file_path && Storage::disk('public')->exists($certificate->file_path)) {
            Storage::disk('public')->delete($certificate->file_path);
        }

        $certificate->delete();

        return redirect()->route('admin.certificates.index')
            ->with('success', 'Sertifikat berhasil dihapus');
    }
}
