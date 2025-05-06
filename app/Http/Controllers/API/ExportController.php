<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use League\Csv\Writer;
use App\Models\User;
use App\Models\UserProfile;
use App\Http\Resources\DataExportResource;


class ExportController extends Controller
{
    public function data_export()
    {
       
        $users = User::all();
        return response()->json([
            "success" => true,
            "data" => DataExportResource::collection($users),
        ]);

    }

    public function exportToCSV()
    {
        // Generate CSV data
        $data = [
            ['Name', 'Email'],
            ['John Doe', 'johndoe@example.com'],
            ['Jane Smith', 'janesmith@example.com'],
            // Add your data here
        ];

        // Create a CSV file and add data
        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        $csv->insertAll($data);

        // Set CSV response headers
        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=export.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        return response($csv->getContent(), 200, $headers);
    }
}
