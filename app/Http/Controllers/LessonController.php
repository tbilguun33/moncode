<?php

namespace App\Http\Controllers;

use App\Enums\EditorType;
use App\Models\Lesson;
use App\Models\ProgressTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function show(Lesson $lesson): View
    {
        $lesson->load('category.course');

        $course = $lesson->category->course;
        $isTextBased = $course->editor_type !== null;

        // Text tutorials (HTML/CSS/C++) are marked complete simply by being
        // read, matching the "унших хичээл" (reading lesson) model. Video
        // lessons (Unity) still earn completion by watching through, tracked
        // client-side via the YouTube IFrame API.
        if ($isTextBased && auth()->check()) {
            ProgressTracker::firstOrCreate(
                ['user_id' => auth()->id(), 'lesson_id' => $lesson->id],
                ['completed' => true, 'completed_at' => now()],
            );
        }

        $isCompleted = auth()->check() && ProgressTracker::query()
            ->where('user_id', auth()->id())
            ->where('lesson_id', $lesson->id)
            ->where('completed', true)
            ->exists();

        [$previousLesson, $nextLesson] = $this->siblingLessons($lesson, $course->id);

        // Full course table of contents, for the persistent sidebar.
        $categories = $course->categories()
            ->orderBy('order')
            ->with(['lessons' => fn ($query) => $query->orderBy('order')])
            ->get();

        return view('lessons.show', [
            'lesson' => $lesson,
            'course' => $course,
            'categories' => $categories,
            'isCompleted' => $isCompleted,
            'hasTryIt' => $isTextBased,
            'isTextBased' => $isTextBased,
            'previousLesson' => $previousLesson,
            'nextLesson' => $nextLesson,
        ]);
    }

    public function editor(Lesson $lesson): View
    {
        $lesson->load('category.course');

        abort_if($lesson->category->course->editor_type === null, 404);

        return view('lessons.editor', [
            'lesson' => $lesson,
            'isCpp' => $lesson->category->course->editor_type === EditorType::Cpp,
        ]);
    }

    /**
     * Called automatically by the player once a lesson's video has been
     * watched through to the end — completion is earned, not clicked.
     */
    public function markComplete(Request $request, Lesson $lesson): JsonResponse
    {
        ProgressTracker::updateOrCreate(
            ['user_id' => $request->user()->id, 'lesson_id' => $lesson->id],
            ['completed' => true, 'completed_at' => now()],
        );

        return response()->json(['status' => 'completed']);
    }

    /**
     * @return array{0: ?Lesson, 1: ?Lesson}
     */
    private function siblingLessons(Lesson $lesson, int $courseId): array
    {
        $orderedLessons = Lesson::query()
            ->whereHas('category', fn ($query) => $query->where('course_id', $courseId))
            ->with('category')
            ->get()
            ->sortBy(fn (Lesson $l) => sprintf('%05d-%05d', $l->category->order, $l->order))
            ->values();

        $currentIndex = $orderedLessons->search(fn (Lesson $l) => $l->id === $lesson->id);

        return [
            $currentIndex > 0 ? $orderedLessons[$currentIndex - 1] : null,
            $currentIndex < $orderedLessons->count() - 1 ? $orderedLessons[$currentIndex + 1] : null,
        ];
    }
}
