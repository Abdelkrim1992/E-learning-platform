<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectsController extends Controller
{
    public function index()
    {
        $projects = Project::with('projectImage')->get()->map(function ($project) {
            // Get the latest image if available
            $latestImage = $project->projectImage()->latest('created_at')->first();
            $project->project_image_url = $latestImage ? asset('storage/' . $latestImage->project_image) : null;
            return $project;
        });
    
        return response()->json([
            'status' => 200,
            'data' => $projects
        ]);
    }
    

    public function store(Request $request)
    {
        // Validate input
        $request->validate([
            'project_description' => 'required|string|max:2000',
            'project_tasks' => 'required|string|max:2000',
            'project_name' => 'required|string|max:255',
            'budjet' => 'required|integer',
            'dead_line' => 'required|date',
            'image' => 'nullable|array', // Expecting an array of images
            'image.*' => 'image|mimes:jpg,jpeg,png|max:2048', // Validate each image
        ]);

        // Save the course first
        $project = new Project();

        if (!$project) {
            return response()->json(['status' => 404, 'message' => 'Course not found']);
        }

        $project->project_description = $request->project_description;
        $project->project_tasks = $request->project_tasks;
        $project->project_name = $request->project_name;
        $project->dead_line = $request->dead_line;
        $project->budjet = $request->budjet;
        $project->save();

        // Save each image
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $image) {
                $uniqueName = time() . '-' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('images', $uniqueName, 'public');

                ProjectImage::create([
                    'project_id' => $project->id,
                    'project_image' => $imagePath,
                ]);
            }
        }

        return response()->json(['status' => 200, 'message' => 'project updated successfully']);
    }

    public function show($id)
    {
        // Find the project by ID, including its related image
        $project = Project::with('projectImage')->find($id);
    
        if (!$project) {
            return response()->json(['status' => 404, 'message' => 'Project not found']);
        }
    
        // Add image URL to the project response
        $project->project_image_url = $project->projectImage ? asset('storage/' . $project->projectImage->project_image) : null;
    
        return response()->json([
            'status' => 200,
            'data' => $project
        ]);
    }
       
    public function update(Request $request, $id)
    {
        // Validate input
        $request->validate([
            'project_description' => 'required|string|max:2000',
            'project_tasks' => 'required|string|max:2000',
            'project_name' => 'required|string|max:255',
            'budjet' => 'required|integer',
            'dead_line' => 'required|date',
            'image' => 'nullable|array', // Expecting an array of images
        ]);

        $project = Project::find($id);

        if (!$project) {
            return response()->json(['status' => 404, 'message' => 'Course not found']);
        }

        $project->project_description = $request->project_description;
        $project->project_tasks = $request->project_tasks;
        $project->project_name = $request->project_name;
        $project->dead_line = $request->dead_line;
        $project->budjet = $request->budjet;
        

        // Save each image
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $image) {
                $uniqueName = time() . '-' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('images', $uniqueName, 'public');

                ProjectImage::create([
                    'project_id' => $project->id,
                    'project_image' => $imagePath,
                ]);
            }
        }

        $project->update($request->except('image'));

        $project->save();

        return response()->json(['status' => 200, 'message' => 'project updated successfully']);
    }

    public function destroy($id)
    {
        $project = Project::find($id);

        if ($project) {
            // Delete associated images
            ProjectImage::where('project_id', $project->id)->get()->each(function ($image) {
                Storage::disk('public')->delete($image->project_image);
                $image->delete();
            });

            // Delete the course
            $project->delete();

            return response()->json([
                'status' => 200,
                'message' => 'project deleted successfully',
                'data' => []
            ]);
        }

        return response()->json(['status' => 404, 'message' => 'project not found', 'data' => []]);
    }
}
