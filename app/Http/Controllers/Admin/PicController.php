<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pic;
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
            'role' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

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
            'role' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

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
}
