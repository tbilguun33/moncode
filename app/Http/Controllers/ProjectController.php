<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        $projects = Project::query()
            ->with(['user', 'images', 'likes', 'comments.user'])
            ->latest()
            ->paginate(9);

        return view('projects.index', [
            'projects' => $projects,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'images' => ['nullable', 'array', 'max:3'],
            'images.*' => ['image', 'max:4096'],
        ]);

        $project = $request->user()->projects()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
        ]);

        foreach ($request->file('images', []) as $index => $image) {
            $project->images()->create([
                'path' => $image->store('projects', 'public'),
                'order' => $index,
            ]);
        }

        return redirect()->route('projects.index')->with('status', 'Төслийг амжилттай нийтэллээ!');
    }

    public function react(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:like,dislike'],
        ]);

        $existing = Like::query()
            ->where('user_id', $request->user()->id)
            ->where('project_id', $project->id)
            ->first();

        if ($existing && $existing->type->value === $data['type']) {
            $existing->delete();
        } else {
            Like::updateOrCreate(
                ['user_id' => $request->user()->id, 'project_id' => $project->id],
                ['type' => $data['type']],
            );
        }

        return back();
    }

    public function storeComment(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $project->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        return back();
    }
}
