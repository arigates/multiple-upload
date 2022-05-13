<?php

namespace App\Http\Controllers;

use App\Models\ProjectImage;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function getImageByProjectId($projectId)
    {
        $images = ProjectImage::where('project_id', $projectId)->get();

        return response()->json($images);
    }

    public function destroy(ProjectImage $image)
    {
        Storage::disk('local')->delete('/projects/'.$image->image_name);

        return response()->json($image->delete(), 200);
    }
}
