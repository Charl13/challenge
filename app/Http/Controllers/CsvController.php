<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\ProcessRecordsFile;

class CsvController extends Controller
{
    /**
     * Move file to storage location and queue
     * a job to process the uploaded file.
     *
     * @param Request $request HTTP request.
     */
    public function upload(Request $request)
    {
        if (!$request->hasFile('users')) {
            return 'No file has been found in the request.';
        }
        $file = $request->file('users');

        $name = sprintf('users_%s.json',  now()->timestamp);
        $path = storage_path('app');

        $file->move($path, $name);

        dispatch(new ProcessRecordsFile($name));

        return 'File successfully received.';
    }
}
