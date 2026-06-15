<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\EmailTemplateAttachment;
use App\Models\EmailLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $templates = EmailTemplate::withCount('attachments')->orderBy('trigger_key')->get();
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
            'attachments.*' => 'file|max:10240',
        ]);

        $template = EmailTemplate::create([
            'name'        => $request->name,
            'trigger_key' => $request->trigger_key,
            'subject'     => $request->subject,
            'body'        => $request->body,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        $this->handleAttachmentUploads($request, $template);

        return redirect()->route('admin.email-templates.index')
            ->with('success', 'Template email berhasil dibuat.');
    }

    public function edit(EmailTemplate $emailTemplate)
    {
        $allKeys       = EmailTemplate::$triggerLabels;
        $availableKeys = $allKeys;
        $template      = $emailTemplate->load('attachments');
        $selectedKey   = $emailTemplate->trigger_key;
        return view('admin.email-templates.form', compact('template', 'allKeys', 'availableKeys', 'selectedKey'));
    }

    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'subject'       => 'required|string|max:255',
            'body'          => 'required|string',
            'is_active'     => 'boolean',
            'attachments.*' => 'file|max:10240',
        ]);

        $emailTemplate->update([
            'name'      => $request->name,
            'subject'   => $request->subject,
            'body'      => $request->body,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Hapus attachment yang diminta
        if ($request->has('delete_attachments')) {
            foreach ($request->delete_attachments as $attachId) {
                $att = EmailTemplateAttachment::where('id', $attachId)
                        ->where('email_template_id', $emailTemplate->id)->first();
                if ($att) {
                    Storage::delete($att->stored_path);
                    $att->delete();
                }
            }
        }

        $this->handleAttachmentUploads($request, $emailTemplate);

        return redirect()->route('admin.email-templates.index')
            ->with('success', 'Template email berhasil diperbarui.');
    }

    public function destroy(EmailTemplate $emailTemplate)
    {
        foreach ($emailTemplate->attachments as $att) {
            Storage::delete($att->stored_path);
        }
        $emailTemplate->delete();
        return redirect()->route('admin.email-templates.index')
            ->with('success', 'Template email berhasil dihapus.');
    }

    public function preview(Request $request, EmailTemplate $emailTemplate)
    {
        $vars = [
            'nama_artikel'       => 'Judul Artikel Contoh - Lorem Ipsum Dolor Sit Amet',
            'kode_submit'        => 'BKD2024001',
            'id_artikel'         => 'ART-2024-001',
            'nama_jurnal'        => 'Jurnal Pendidikan Indonesia',
            'url_jurnal'         => 'https://journal.example.org/index.php/JPI',
            'nama_penulis'       => 'Prof. Budi Santoso, M.Pd',
            'username_author'    => 'budi.santoso',
            'password_author'    => 'pass_author123',
            'nama_pic'           => 'Dr. Siti Rahayu, M.Pd',
            'email_pic'          => 'siti.rahayu@apji.org',
            'nama_tahap'         => EmailTemplate::$triggerLabels[$emailTemplate->trigger_key] ?? $emailTemplate->trigger_key,
            'tanggal'            => now()->format('d/m/Y H:i'),
            'username_editor'    => 'editor_user',
            'password_editor'    => 'pass1234',
            'username_reviewer1' => 'reviewer1_user',
            'password_reviewer1' => 'rev_pass1',
            'username_reviewer2' => 'reviewer2_user',
            'password_reviewer2' => 'rev_pass2',
            'app_name'           => config('app.name'),
        ];
        $rendered = $emailTemplate->render($vars);
        $rendered['attachments'] = $emailTemplate->attachments->map(fn($a) => [
            'id'   => $a->id,
            'name' => $a->original_name,
            'size' => $this->formatBytes($a->size),
        ])->values()->toArray();
        return response()->json($rendered);
    }

    public function toggleActive(EmailTemplate $emailTemplate)
    {
        $emailTemplate->update(['is_active' => !$emailTemplate->is_active]);
        return response()->json(['success' => true, 'is_active' => $emailTemplate->is_active]);
    }

    public function deleteAttachment(EmailTemplateAttachment $attachment)
    {
        Storage::delete($attachment->stored_path);
        $attachment->delete();
        return response()->json(['success' => true]);
    }

    public function logs(Request $request)
    {
        $query = EmailLog::with('submission:id,kode_submit,judul_artikel')
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('trigger_key')) {
            $query->where('trigger_key', $request->trigger_key);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('recipient_email', 'like', "%{$s}%")
                  ->orWhere('recipient_name', 'like', "%{$s}%")
                  ->orWhere('subject', 'like', "%{$s}%");
            });
        }
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $logs        = $query->paginate(50)->withQueryString();
        $triggerLabels = EmailTemplate::$triggerLabels;

        // Summary counts
        $summary = EmailLog::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.email-logs.index', compact('logs', 'triggerLabels', 'summary'));
    }

    private function handleAttachmentUploads(Request $request, EmailTemplate $template): void
    {
        if (!$request->hasFile('attachments')) return;
        foreach ($request->file('attachments') as $file) {
            $path = $file->store('email-attachments/' . $template->id);
            EmailTemplateAttachment::create([
                'email_template_id' => $template->id,
                'original_name'     => $file->getClientOriginalName(),
                'stored_path'       => $path,
                'mime_type'         => $file->getMimeType(),
                'size'              => $file->getSize(),
            ]);
        }
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }
}