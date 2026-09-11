<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\ProgressTracker;
use App\Models\Project;
use App\Models\User;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Rank tiers for the "Zero to Hero" roadmap, evenly spaced across the
     * user's overall lesson-completion percentage.
     */
    private const TIERS = [
        ['key' => 'stone', 'label' => 'Stone', 'icon' => '🪨', 'description' => 'Алгоритм болон программчлалын үндсэн ойлголтыг эмх цэгцтэйгээр эзэмших эхний түвшин.'],
        ['key' => 'bronze', 'label' => 'Bronze', 'icon' => '🥉', 'description' => 'Анхан шатны бодлогуудыг бие даан бодож сурах түвшин.'],
        ['key' => 'silver', 'label' => 'Silver', 'icon' => '🥈', 'description' => 'Дата бүтэц болон илүү нарийн логиктой бодлогуудыг шийдэх түвшин.'],
        ['key' => 'gold', 'label' => 'Gold', 'icon' => '🥇', 'description' => 'Дунд шатны алгоритмуудыг өөртөө итгэлтэйгээр ашиглах түвшин.'],
        ['key' => 'plat', 'label' => 'Diamond', 'icon' => '💎', 'description' => 'Ахисан түвшний бодлогуудыг цөөн алдаагаар шийдэх түвшин.'],
        ['key' => 'diamond', 'label' => 'Plat', 'icon' => '💠', 'description' => 'Платформын бүх сэдвийг бүрэн эзэмшсэн, ярилцлагад бэлэн түвшин.'],
    ];

    public function index(): View
    {
        $courses = Course::query()
            ->orderBy('order')
            ->with('categories.lessons')
            ->get();

        $completedLessonIds = $this->completedLessonIds();

        $courseStats = $courses->mapWithKeys(function (Course $course) use ($completedLessonIds) {
            $lessonIds = $course->categories->flatMap->lessons->pluck('id');
            $total = $lessonIds->count();
            $completed = $lessonIds->intersect($completedLessonIds)->count();

            return [$course->id => [
                'total' => $total,
                'completed' => $completed,
                'percent' => $total > 0 ? (int) round($completed / $total * 100) : 0,
            ]];
        });

        $totalLessons = Lesson::count();
        $overallPercent = $totalLessons > 0
            ? round(count($completedLessonIds) / $totalLessons * 100, 1)
            : 0.0;

        $tierCount = count(self::TIERS);
        $tierIndex = $overallPercent <= 0
            ? 0
            : (int) min(floor($overallPercent / (100 / $tierCount)), $tierCount - 1);

        $siteStats = [
            'users' => User::count(),
            'lessons' => $totalLessons,
            'courses' => $courses->count(),
            'projects' => Project::count(),
            'completedLessons' => ProgressTracker::where('completed', true)->count(),
        ];

        return view('home', [
            'courses' => $courses,
            'courseStats' => $courseStats,
            'tiers' => self::TIERS,
            'currentTierKey' => self::TIERS[$tierIndex]['key'],
            'overallPercent' => $overallPercent,
            'siteStats' => $siteStats,
        ]);
    }

    /**
     * @return array<int, int>
     */
    private function completedLessonIds(): array
    {
        if (! auth()->check()) {
            return [];
        }

        return ProgressTracker::query()
            ->where('user_id', auth()->id())
            ->where('completed', true)
            ->pluck('lesson_id')
            ->all();
    }
}
