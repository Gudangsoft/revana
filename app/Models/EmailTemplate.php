<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = ['name', 'trigger_key', 'subject', 'body', 'is_active'];
    protected $casts    = ['is_active' => 'boolean'];

    public static array $triggerLabels = [
        'assign_editor1'    => 'Penugasan Editor 1',
        'assign_author1'    => 'Penugasan Author 1',
        'assign_editor2'    => 'Penugasan Editor 2',
        'assign_reviewer1'  => 'Penugasan Reviewer 1',
        'assign_reviewer2'  => 'Penugasan Reviewer 2',
        'assign_editor3'    => 'Penugasan Editor 3',
        'assign_author2'    => 'Penugasan Author 2',
        'assign_production' => 'Penugasan Production',
        'assign_validator'  => 'Penugasan Validator',
        'validate_editor1'    => 'Validasi Selesai: Editor 1',
        'validate_author1'    => 'Validasi Selesai: Author 1',
        'validate_editor2'    => 'Validasi Selesai: Editor 2',
        'validate_reviewer1'  => 'Validasi Selesai: Reviewer 1',
        'validate_reviewer2'  => 'Validasi Selesai: Reviewer 2',
        'validate_editor3'    => 'Validasi Selesai: Editor 3',
        'validate_author2'    => 'Validasi Selesai: Author 2',
        'validate_production' => 'Validasi Selesai: Production',
        'validate_validator'  => 'Validasi Selesai: Validator',
    ];

    /** Render subject & body dengan substitusi variabel */
    public function render(array $vars): array
    {
        $subject = $this->subject;
        $body    = $this->body;
        foreach ($vars as $key => $value) {
            $subject = str_replace('{' . $key . '}', (string) $value, $subject);
            $body    = str_replace('{' . $key . '}', (string) $value, $body);
        }
        return ['subject' => $subject, 'body' => $body];
    }

    /** Ambil template aktif berdasarkan trigger key */
    public static function findActive(string $triggerKey): ?self
    {
        return static::where('trigger_key', $triggerKey)->where('is_active', true)->first();
    }
}