<?php

namespace App\Http\Controllers;

use Response;

class StorageController extends Controller
{
    public function show(): \Illuminate\Http\Response
    {
        $args = func_get_args();
        $path = implode('/', $args);
        $full_path = storage_path('app/' . $path);
        $file_name = end($args);

        $mime = mime_content_type($full_path);
        $headers = array(
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.$file_name.'"'
        );

        return Response::make(file_get_contents($full_path), 200, $headers);
    }
}
