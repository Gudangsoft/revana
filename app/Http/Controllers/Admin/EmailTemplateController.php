<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $templates = EmailTemplate::orderBy('trigger_key')->get();
        $allKeys   = EmailTemplate::$triggerLabels;
        return view('admin.email-templates.index', compact('templates', 'allKeys'));
    }

    public function create(Request $request)
    {
        $allKeys       = EmailTemplate::$triggerLabels;
        $existingKeys  = EmailTemplate::pluck('trigger_key')->toArray();
        $availableKeys = array_diff_key($allKeys, array_flip($existingKeys));
        $template      = null;
        $selectedKey   = $request->query('trigger_key', '');
        return view('admin.email-templates.form', compact('template', 'allKeys', 'availableKeys', 'selectedKey'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'trigger_key' => 'required|string|in:' . implode(',', array_keys(EmailTemplate::$triggerLabels)) . '|unique:email_templates,trigger_key',
            'subject'     => 'required|string|max:255',
            'body'        => 'required|string',
            'is_active'   => 'boolean',
        ]);

        EmailTemplate::create([
            'name'        => $request->name,
            'trigger_key' => $request->trigger_key,
            'subject'     => $request->subject,
            'body'        => $request->body,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.email-templates.index')
            ->with('success', 'Template email berhasil dibuat.');
    }

    public function edit(EmailTemplate $emailTemplate)
    {
        $allKeys      = EmailTemplate::$triggerLabels;
        $availableKeys = $allKeys; // semua tersedia saat edit (boleh tetap di key yang sama)
        $template     = $emailTemplate;
        $selectedKey  = $emailTemplate->trigger_key;
        return view('admin.email-templates.form', compact('template', 'allKeys', 'availableKeys', 'selectedKey'));
    }

    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $request->validate([
            'name'      => 'required|string|max:100',
            'subject'   => 'required|string|max:255',
            'body'      => 'required|string',
            'is_active' => 'boolean',
        ]);

        $emailTemplate->update([
            'name'      => $request->name,
            'subject'   => $request->subject,
            'body'      => $request->body,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.email-templates.index')
            ->with('success', 'Template email berhasil diperbarui.');
    }

    public function destroy(EmailTemplate $emailTemplate)
    {
        $emailTemplate->delete();
        return redirect()->route('admin.email-templates.index')
            ->with('success', 'Template email berhasil dihapus.');
    }

    public function preview(Request $request, EmailTemplate $emailTemplate)
    {
        $vars = [
            'nama_artikel'      => 'Judul Artikel Contoh - Lorem Ipsum Dolor Sit Amet',
            'kode_submit'       => 'BKD2024001',
            'id_artikel'        => 'ART-2024-001',
            'nama_pic'          => 'Dr. Siti Rahayu, M.Pd',
            'email_pic'         => 'siti.rahayu@apji.org',
            'nama_tahap'        => EmailTemplate::$triggerLabels[$emailTemplate->trigger_key] ?? $emailTemplate->trigger_key,
            'tanggal'           => now()->format('d/m/Y H:i'),
            'username_editor'   => 'editor_user',
            'password_editor'   => 'pass1234',
            'username_reviewer1'=> 'reviewer1_user',
            'password_reviewer1'=> 'rev_pass1',
            'username_reviewer2'=> 'reviewer2_user',
            'password_reviewer2'=> 'rev_pass2',
            'app_name'          => config('app.name'),
        ];
        $rendered = $emailTemplate->render($vars);
        return response()->json($rendered);
    }

    public function toggleActive(EmailTemplate $emailTemplate)
    {
        $emailTemplate->update(['is_active' => !$emailTemplate->is_active]);
        return response()->json(['success' => true, 'is_active' => $emailTemplate->is_active]);
    }
}