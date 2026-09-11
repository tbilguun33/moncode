<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\ProgressTracker;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function show(Course $course): View
    {
        $course->load(['categories' => function ($query) {
            $query->orderBy('order')->with(['lessons' => function ($lessonQuery) {
                $lessonQuery->orderBy('order');
            }]);
        }]);

        $completedLessonIds = auth()->check()
            ? ProgressTracker::query()
                ->where('user_id', auth()->id())
                ->where('completed', true)
                ->pluck('lesson_id')
                ->all()
            : [];

        $categoryStats = $course->categories->mapWithKeys(function ($category) use ($completedLessonIds) {
            $total = $category->lessons->count();
            $completed = $category->lessons->pluck('id')->intersect($completedLessonIds)->count();

            return [$category->id => [
                'total' => $total,
                'completed' => $completed,
                'percent' => $total > 0 ? (int) round($completed / $total * 100) : 0,
            ]];
        });

        $totalLessons = $course->categories->sum(fn ($category) => $category->lessons->count());
        $completedTotal = $categoryStats->sum('completed');

        return view('courses.show', [
            'course' => $course,
            'categoryStats' => $categoryStats,
            'completedLessonIds' => $completedLessonIds,
            'totalLessons' => $totalLessons,
            'completedTotal' => $completedTotal,
            'overallPercent' => $totalLessons > 0 ? (int) round($completedTotal / $totalLessons * 100) : 0,
        ]);
    }
}
