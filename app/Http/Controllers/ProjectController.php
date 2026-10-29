<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Contracts\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        return view('projects.index', [
            'projects' => Project::query()->published()->ordered()->paginate(12),
        ]);
    }

    public function show(string $slug): View
    {
        // Drafts and scheduled projects are indistinguishable from missing ones.
        $project = Project::query()->published()->where('slug', $slug)->firstOrFail();

        return view('projects.show', ['project' => $project]);
    }
}
