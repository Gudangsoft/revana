<?php

namespace App\Exports;

use App\Models\ReviewRequest;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReviewRequestsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $status;
    protected $search;

    public function __construct($status = null, $search = null)
    {
        $this->status = $status;
        $this->search = $search;
    }

    public function collection()
    {
        $query = ReviewRequest::with(['reviewer.fieldOfStudy', 'approver'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($this->status && in_array($this->status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $this->status);
        }

        // Search
        if ($this->search) {
            $query->whereHas('reviewer', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('institution', 'like', '%' . $this->search . '%');
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tanggal Pengajuan',
            'Nama Reviewer',
            'Email',
            'Institusi',
            'Bidang Ilmu',
            'Bahasa Artikel',
            'Jumlah Jurnal',
            'Lama Hari',
            'Status',
            'Catatan Reviewer',
            'Catatan Admin',
            'Diproses Oleh',
            'Tanggal Diproses',
        ];
    }

    public function map($request): array
    {
        $status = [
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak'
        ];

        return [
            $request->id,
            $request->created_at->format('d/m/Y H:i'),
            $request->reviewer->name ?? '-',
            $request->reviewer->email ?? '-',
            $request->reviewer->institution ?? '-',
            $request->reviewer->fieldOfStudy->name ?? '-',
            is_array($request->reviewer->article_languages) 
                ? implode(', ', $request->reviewer->article_languages) 
                : '-',
            $request->number_of_journals,
            $request->number_of_days,
            $status[$request->status] ?? $request->status,
            $request->notes ?? '-',
            $request->admin_notes ?? '-',
            $request->approver->name ?? '-',
            $request->approved_at ? $request->approved_at->format('d/m/Y H:i') : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
