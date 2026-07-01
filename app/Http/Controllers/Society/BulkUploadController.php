<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Models\Society;
use Illuminate\Http\Request;
use Throwable;
use ZipArchive;

class BulkUploadController extends Controller
{
    public function index()
    {
        $society = Society::firstOrFail();

        $uploads = $society->billUploads()->latest()->get();

        return view('society.billing.bulk-upload', compact('society', 'uploads'));
    }

    public function upload(Request $request)
    {
        $society = Society::firstOrFail();

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        $file = $request->file('file');
        $storedPath = $file->store('bill-uploads', 'public');

        $society->billUploads()->create([
            'original_name' => $file->getClientOriginalName(),
            'stored_path' => $storedPath,
            'uploaded_by' => 'Super Admin',
            'records_count' => $this->countRows($file),
            'status' => 'validated',
        ]);

        return redirect()->route('society.billing.bulk-upload')
            ->with('success', 'File uploaded and validated successfully.');
    }

    public function sample()
    {
        $headings = [
            'Flat No', 'Member Mobile', 'Bill Month', 'Bill Date',
            'Due Date', 'Charge Head', 'Amount', 'Notes',
        ];

        $rows = [
            ['A-101', '9876543210', 'June 2025', '01/06/2025', '10/06/2025', 'Maintenance', '2500', 'Monthly maintenance'],
            ['A-102', '9876543211', 'June 2025', '01/06/2025', '10/06/2025', 'Maintenance', '1800', ''],
            ['B-203', '9876543212', 'June 2025', '01/06/2025', '10/06/2025', 'Maintenance', '2500', ''],
        ];

        $callback = function () use ($headings, $rows) {
            $output = fopen('php://output', 'w');
            fputcsv($output, $headings);
            foreach ($rows as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
        };

        return response()->streamDownload($callback, 'maintenance_bills_sample.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Count data rows in an uploaded .xlsx file using the native ZipArchive
     * reader (xlsx is a zip container). Returns 0 when the file cannot be read.
     */
    private function countRows($file): int
    {
        try {
            if (! class_exists(ZipArchive::class)) {
                return 0;
            }

            $zip = new ZipArchive;
            if ($zip->open($file->getRealPath()) !== true) {
                return 0;
            }

            $xml = $zip->getFromName('xl/worksheets/sheet1.xml');
            $zip->close();

            if ($xml === false) {
                return 0;
            }

            $rows = substr_count($xml, '<row ');

            return max(0, $rows - 1); // exclude the header row
        } catch (Throwable $e) {
            return 0;
        }
    }
}
