<?php

namespace App\Services;

use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Storage;
use Exception;

class PdfMergerService
{
    /**
     * Merge multiple PDF files into one
     * 
     * @param array $pdfPaths Array of PDF file paths
     * @param string $outputPath Output path for merged PDF
     * @return string|false Path to merged PDF or false on failure
     */
    public function mergePdfs(array $pdfPaths, string $outputPath)
    {
        try {
            $pdf = new Fpdi();
            
            // Loop through all PDF files
            foreach ($pdfPaths as $pdfPath) {
                // Get full path from storage
                $fullPath = Storage::disk('public')->path($pdfPath);
                
                // Check if file exists
                if (!file_exists($fullPath)) {
                    throw new Exception("File not found: {$pdfPath}");
                }
                
                // Get the page count
                $pageCount = $pdf->setSourceFile($fullPath);
                
                // Import all pages
                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    // Import a page
                    $templateId = $pdf->importPage($pageNo);
                    
                    // Get the size of the imported page
                    $size = $pdf->getTemplateSize($templateId);
                    
                    // Add a page (landscape or portrait based on the imported page)
                    if ($size['width'] > $size['height']) {
                        $pdf->AddPage('L', [$size['width'], $size['height']]);
                    } else {
                        $pdf->AddPage('P', [$size['width'], $size['height']]);
                    }
                    
                    // Use the imported page
                    $pdf->useTemplate($templateId);
                }
            }
            
            // Create directory if not exists
            $directory = dirname(Storage::disk('public')->path($outputPath));
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            
            // Output the PDF to file
            $outputFullPath = Storage::disk('public')->path($outputPath);
            $pdf->Output('F', $outputFullPath);
            
            return $outputPath;
            
        } catch (Exception $e) {
            \Log::error('PDF Merge Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validate if file is a valid PDF
     * 
     * @param string $filePath
     * @return bool
     */
    public function isValidPdf(string $filePath): bool
    {
        $fullPath = Storage::disk('public')->path($filePath);
        
        if (!file_exists($fullPath)) {
            return false;
        }
        
        // Check file extension
        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        if ($extension !== 'pdf') {
            return false;
        }
        
        // Check PDF header
        $handle = fopen($fullPath, 'r');
        $header = fread($handle, 4);
        fclose($handle);
        
        return $header === '%PDF';
    }
    
    /**
     * Get PDF page count
     * 
     * @param string $filePath
     * @return int
     */
    public function getPageCount(string $filePath): int
    {
        try {
            $pdf = new Fpdi();
            $fullPath = Storage::disk('public')->path($filePath);
            return $pdf->setSourceFile($fullPath);
        } catch (Exception $e) {
            \Log::error('Get Page Count Error: ' . $e->getMessage());
            return 0;
        }
    }
}
