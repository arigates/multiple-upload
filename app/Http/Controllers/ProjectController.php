<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectImage;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Renderable
     */
    public function index(): Renderable
    {
        $projects = Project::all();

        return view('project')->with([
            'projects' => $projects
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $messages = [
            'name.required' => 'Nama project wajib isi',
            'images.*.mimes' => 'Tipe gambar harus jpg,png,jpeg,JPG,JPEG,PNG',
            'images.*.max' => 'Maksimal ukuran gambar 1MB'
        ];

        $this->validate($request, [
            'name' => 'required',
            'images' => 'required|array',
            'images.*' => 'mimes:jpg,png,jpeg,JPG,JPEG,PNG|max:1028'
        ], $messages);

        $images = [];
        foreach ($request->images as $image) {
            $image_name = Carbon::now()->timestamp . '_' . uniqid() . '.'. $image->extension();
            $image->move(Storage::disk('local')->path('projects'), $image_name);
            $images[] = ProjectImage::make(['image_name' => $image_name]);
        }

        $project = Project::create($request->only('name'));
        $project->images()->saveMany($images);

        return response()->json($project);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Project $project
     * @return JsonResponse
     */
    public function edit(Project $project): JsonResponse
    {
        $project = $project->load('images');

        return response()->json($project);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Project $project
     * @return JsonResponse
     */
    public function update(Request $request, Project $project)
    {
        $messages = [
            'name.required' => 'Nama project wajib isi',
            'images.*.mimes' => 'Tipe gambar harus jpg,png,jpeg,JPG,JPEG,PNG',
            'images.*.max' => 'Maksimal ukuran gambar 1MB'
        ];

        $this->validate($request, [
            'name' => 'required',
            'images' => 'nullable|array',
            'images.*' => 'mimes:jpg,png,jpeg,JPG,JPEG,PNG|max:1028'
        ], $messages);

        $images = [];
        if ($request->has('images')) {
            foreach ($request->images as $image) {
                $image_name = Carbon::now()->timestamp . '_' . uniqid() . '.'. $image->extension();
                $image->move(Storage::disk('local')->path('projects'), $image_name);
                $images[] = ProjectImage::make(['image_name' => $image_name]);
            }
        }

        $project->name = $request->name;
        $project->images()->saveMany($images);
        $project->save();

        return response()->json($project);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Project $project
     * @return JsonResponse
     */
    public function destroy(Project $project)
    {
        $project = $project->load('images');

        try {
            foreach ($project->images as $image) {
                Storage::disk('local')->delete('/projects/'.$image->image_name);
                $image->delete();
            }

            $project->delete();
            DB::commit();

            return response()->json(true);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());

            return response()->json(false, 400);
        }
    }
}
