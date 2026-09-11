<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\ProgressTracker;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();

        $totalLessons = Lesson::count();
        $completed = ProgressTracker::query()
            ->where('user_id', $user->id)
            ->where('completed', true)
            ->count();

        return view('profile.show', [
            'completed' => $completed,
            'totalLessons' => $totalLessons,
            'percent' => $totalLessons > 0 ? (int) round($completed / $totalLessons * 100) : 0,
            'projectsCount' => $user->projects()->count(),
        ]);
    }
}
