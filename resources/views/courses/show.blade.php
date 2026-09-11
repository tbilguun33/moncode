@extends('layouts.app')

@section('title', $course->name . ' — ' . config('app.name'))

@section('content')
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <nav class="mb-6 flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
        <a href="{{ route('home') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Эхлэл</a>
        <span>/</span>
        <span class="font-medium text-slate-700 dark:text-slate-200">{{ $course->name }}</span>
    </nav>

    @php $isLocked = in_array($course->slug, ['unity'], true); @endphp

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <div class="lg:col-span-1">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all duration-300 lg:sticky lg:top-24 dark:border-slate-800/80 dark:bg-slate-900/60 dark:shadow-[0_0_30px_rgba(99,102,241,0.08)] dark:backdrop-blur-xl">
                <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $course->name }}</h1>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ $course->description }}</p>

                <div class="mt-6">
                    <div class="flex items-center justify-between text-sm font-medium text-slate-600 dark:text-slate-400">
                        <span>Явц</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $overallPercent }}%</span>
                    </div>
                    <div class="mt-2 h-2.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                        <div class="h-full rounded-full bg-linear-to-r from-indigo-500 via-purple-500 to-emerald-400 transition-all duration-500" style="width: {{ $overallPercent }}%"></div>
                    </div>
                </div>

                <dl class="mt-6 grid grid-cols-2 gap-4 text-center">
                    <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60">
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Хичээл</dt>
                        <dd class="mt-1 text-lg font-bold text-slate-900 dark:text-slate-100">{{ $completedTotal }}/{{ $totalLessons }}</dd>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60">
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Сэдэв</dt>
                        <dd class="mt-1 text-lg font-bold text-slate-900 dark:text-slate-100">{{ $course->categories->count() }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="space-y-4 lg:col-span-2">
            @foreach ($course->categories as $category)
                @php $stats = $categoryStats[$category->id]; @endphp
                <div class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-indigo-300 hover:shadow-lg dark:border-slate-800/80 dark:bg-slate-900/60 dark:shadow-none dark:backdrop-blur-xl dark:hover:border-indigo-500/50 dark:hover:shadow-[0_0_25px_rgba(99,102,241,0.12)]">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full {{ $stats['percent'] === 100 ? 'bg-emerald-500' : 'bg-slate-200 dark:bg-slate-800' }}">
                                @if ($stats['percent'] === 100)
                                    <svg class="h-4 w-4 text-white" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.5 7.5a1 1 0 01-1.42 0l-3.5-3.5a1 1 0 111.42-1.42l2.79 2.79 6.79-6.79a1 1 0 011.42 0z" clip-rule="evenodd" /></svg>
                                @else
                                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ $loop->iteration }}</span>
                                @endif
                            </span>
                            <div>
                                <h2 class="font-bold text-slate-900 dark:text-slate-100">{{ $loop->iteration }}. {{ $category->name }}</h2>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $category->description }}</p>
                            </div>
                        </div>

                        @if ($isLocked)
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-slate-200 text-slate-300 dark:border-slate-700 dark:text-slate-600">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" /></svg>
                            </span>
                        @elseif ($category->lessons->isNotEmpty())
                            <a href="{{ route('lessons.show', $category->lessons->first()) }}"
                               class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition-all duration-300 hover:-translate-y-0.5 hover:bg-slate-50 hover:text-indigo-600 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-indigo-400">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                            </a>
                        @endif
                    </div>

                    <div class="mt-4">
                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                            <div class="h-full rounded-full {{ $stats['percent'] === 100 ? 'bg-emerald-500' : 'bg-linear-to-r from-indigo-500 to-purple-500' }} transition-all duration-500" style="width: {{ $stats['percent'] }}%"></div>
                        </div>
                        <p class="mt-2 text-xs font-medium text-slate-500 dark:text-slate-400">
                            {{ $stats['completed'] }}/{{ $stats['total'] }} унших хичээл &nbsp;·&nbsp; {{ $stats['percent'] }}%
                        </p>
                    </div>

                    @if ($category->lessons->isNotEmpty())
                        <ul class="mt-4 space-y-1 border-t border-slate-100 pt-4 dark:border-slate-800">
                            @foreach ($category->lessons as $lesson)
                                @php $lessonDone = in_array($lesson->id, $completedLessonIds); @endphp
                                <li>
                                    @if ($isLocked)
                                        <span class="flex cursor-not-allowed items-center gap-3 rounded-lg px-2 py-2 text-sm opacity-60">
                                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500">
                                                <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" /></svg>
                                            </span>
                                            <span class="flex-1 truncate font-medium text-slate-500 dark:text-slate-400">
                                                {{ $lesson->title }}
                                            </span>
                                        </span>
                                    @else
                                        <a href="{{ route('lessons.show', $lesson) }}"
                                           class="flex items-center gap-3 rounded-lg px-2 py-2 text-sm transition hover:bg-slate-50 dark:hover:bg-white/5">
                                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full {{ $lessonDone ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500' }}">
                                                @if ($lessonDone)
                                                    <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.5 7.5a1 1 0 01-1.42 0l-3.5-3.5a1 1 0 111.42-1.42l2.79 2.79 6.79-6.79a1 1 0 011.42 0z" clip-rule="evenodd" /></svg>
                                                @else
                                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                                @endif
                                            </span>
                                            <span class="flex-1 truncate {{ $lessonDone ? 'text-slate-500 dark:text-slate-400' : 'font-medium text-slate-700 dark:text-slate-200' }}">
                                                {{ $lesson->title }}
                                            </span>
                                            <svg class="h-4 w-4 shrink-0 text-slate-300 transition group-hover:text-indigo-400 dark:text-slate-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
