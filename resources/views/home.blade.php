@extends('layouts.app')

@section('title', config('app.name') . ' — Хичээлээ дуусгаж, Hero бол')

@section('content')

<section class="relative overflow-hidden bg-slate-50 py-20 dark:bg-slate-950">
    {{-- Ambient glow orbs (dark mode only) --}}
    <div class="pointer-events-none absolute -top-32 -left-32 hidden h-96 w-96 rounded-full bg-indigo-600/20 blur-3xl dark:block"></div>
    <div class="pointer-events-none absolute top-1/2 -right-32 hidden h-96 w-96 rounded-full bg-cyan-500/10 blur-3xl dark:block"></div>
    <div class="pointer-events-none absolute bottom-0 left-1/3 hidden h-72 w-72 rounded-full bg-purple-600/10 blur-3xl dark:block"></div>

    <div class="relative mx-auto max-w-6xl px-4 text-center sm:px-6 lg:px-8">
        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-indigo-600 dark:text-indigo-400">Moncode Roadmap</p>
        <h1 class="mt-3 bg-linear-to-r from-indigo-500 via-purple-500 to-pink-500 bg-clip-text text-4xl font-extrabold tracking-tight text-transparent sm:text-5xl dark:from-indigo-400 dark:via-purple-400 dark:to-pink-400">
            Амжилт чамайг хүлээхгүй, Чи амжилтыг хүлээхгүй
        </h1>
        <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">Хичээлээ дуусгаж, шатлал ахин Hero болоорой</p>

        {{-- Desktop / tablet timeline --}}
        <div class="relative mt-20 hidden md:block">
            <svg class="absolute left-0 right-0 top-6 h-3 w-full overflow-visible" preserveAspectRatio="none" viewBox="0 0 100 12">
                <defs>
                    <linearGradient id="roadmapGradient" x1="0" y1="0" x2="100" y2="0" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="#6366f1" />
                        <stop offset="55%" stop-color="#06b6d4" />
                        <stop offset="100%" stop-color="#10b981" />
                    </linearGradient>
                    <filter id="roadmapGlow" x="-20%" y="-500%" width="140%" height="1100%">
                        <feGaussianBlur stdDeviation="1.4" result="blur" />
                        <feMerge>
                            <feMergeNode in="blur" />
                            <feMergeNode in="SourceGraphic" />
                        </feMerge>
                    </filter>
                </defs>
                <line x1="0" y1="6" x2="100" y2="6" class="stroke-slate-200 dark:stroke-slate-800" stroke-width="3" stroke-linecap="round" />
                <line x1="0" y1="6" x2="{{ min(max($overallPercent, 2), 100) }}" y2="6"
                      stroke="url(#roadmapGradient)" stroke-width="3" stroke-linecap="round" filter="url(#roadmapGlow)" />
            </svg>

            <div class="relative flex items-start justify-between">
                <div class="flex w-16 flex-col items-center">
                    <span class="z-10 flex h-9 w-9 items-center justify-center rounded-full border-2 border-slate-50 bg-white text-[9px] font-bold text-slate-500 shadow-sm dark:border-slate-950 dark:bg-slate-800 dark:text-slate-300 dark:shadow-[0_0_15px_rgba(15,23,42,0.8)]">ZERO</span>
                </div>

                @foreach ($tiers as $tier)
                    @php $isCurrent = $tier['key'] === $currentTierKey; @endphp
                    <div class="flex w-24 flex-col items-center text-center">
                        <span class="relative flex h-12 w-12 items-center justify-center">
                            @if ($isCurrent)
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-indigo-500 opacity-40"></span>
                            @endif
                            <span class="relative z-10 flex h-12 w-12 items-center justify-center rounded-full border-2 text-lg
                                {{ $isCurrent
                                    ? 'border-indigo-400 bg-linear-to-br from-indigo-500 to-purple-600 shadow-[0_0_20px_rgba(99,102,241,0.5)]'
                                    : 'border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:shadow-[0_0_10px_rgba(0,0,0,0.4)]' }}">
                                {{ $tier['icon'] }}
                            </span>
                        </span>
                        <span class="mt-2 text-xs font-semibold {{ $isCurrent ? 'text-indigo-600 dark:text-indigo-300' : 'text-slate-500' }}">
                            {{ $tier['label'] }}
                        </span>
                        @if ($isCurrent)
                            <span class="mt-1 whitespace-nowrap rounded-full bg-linear-to-r from-indigo-500 to-purple-600 px-3 py-1 text-[10px] font-semibold text-white shadow-[0_0_15px_rgba(99,102,241,0.45)]">
                                Та энд байна · {{ $overallPercent }}%
                            </span>
                        @endif
                    </div>
                @endforeach

                <div class="flex w-16 flex-col items-center">
                    <span class="z-10 flex h-9 w-9 items-center justify-center rounded-full border-2 border-slate-50 bg-white text-[8px] font-bold text-slate-500 shadow-sm dark:border-slate-950 dark:bg-slate-800 dark:text-slate-300 dark:shadow-[0_0_15px_rgba(15,23,42,0.8)]">HERO</span>
                </div>
            </div>
        </div>

        {{-- Mobile fallback --}}
        <div class="mt-10 md:hidden">
            <div class="flex items-center justify-between text-xs font-semibold text-slate-500">
                <span>ZERO</span><span>HERO</span>
            </div>
            <div class="mt-2 h-2 rounded-full bg-slate-200 dark:bg-slate-800">
                <div class="h-full rounded-full bg-linear-to-r from-indigo-500 via-cyan-400 to-emerald-400 shadow-[0_0_15px_rgba(99,102,241,0.5)]"
                     style="width: {{ min(max($overallPercent, 2), 100) }}%"></div>
            </div>
            <p class="mt-2 text-sm text-indigo-600 dark:text-indigo-300">Та энд байна: {{ ucfirst($currentTierKey) }} ({{ $overallPercent }}%)</p>
        </div>

        {{-- Milestone cards --}}
        <div class="mt-16 grid grid-cols-2 gap-3 text-left sm:grid-cols-3 lg:grid-cols-6">
            @foreach ($tiers as $tier)
                @php $isCurrent = $tier['key'] === $currentTierKey; @endphp
                <div class="group rounded-2xl border p-4 transition-all duration-300 hover:-translate-y-1
                    {{ $isCurrent
                        ? 'border-indigo-400 bg-indigo-50 shadow-[0_0_20px_rgba(99,102,241,0.15)] dark:border-indigo-500/60 dark:bg-indigo-500/10 dark:shadow-[0_0_20px_rgba(99,102,241,0.2)]'
                        : 'border-slate-200 bg-white hover:border-indigo-300 hover:shadow-md dark:border-slate-800/80 dark:bg-slate-900/60 dark:backdrop-blur-xl dark:hover:border-indigo-500/50 dark:hover:shadow-[0_0_20px_rgba(99,102,241,0.12)]' }}">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">{{ $tier['icon'] }}</span>
                        <span class="font-bold {{ $isCurrent ? 'text-indigo-700 dark:text-indigo-300' : 'text-slate-800 dark:text-slate-200' }}">{{ $tier['label'] }}</span>
                    </div>
                    <p class="mt-2 text-xs leading-relaxed text-slate-500 dark:text-slate-400">{{ $tier['description'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="relative border-y border-slate-200 bg-white px-4 py-14 sm:px-6 lg:px-8 dark:border-slate-800/80 dark:bg-slate-950">
    <div class="mx-auto grid max-w-6xl grid-cols-2 gap-6 sm:grid-cols-4">
        @foreach ([
            ['value' => $siteStats['users'], 'label' => 'Суралцагч', 'icon' => '👤'],
            ['value' => $siteStats['courses'], 'label' => 'Сургалт', 'icon' => '📚'],
            ['value' => $siteStats['lessons'], 'label' => 'Хичээл', 'icon' => '📄'],
            ['value' => $siteStats['completedLessons'], 'label' => 'Дуусгасан хичээл', 'icon' => '✅'],
        ] as $stat)
            <div class="text-center">
                <div class="text-2xl">{{ $stat['icon'] }}</div>
                <div class="mt-2 bg-linear-to-r from-indigo-500 to-purple-500 bg-clip-text text-3xl font-extrabold text-transparent sm:text-4xl dark:from-indigo-400 dark:to-purple-400">
                    {{ number_format($stat['value']) }}+
                </div>
                <p class="mt-1 text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $stat['label'] }}</p>
            </div>
        @endforeach
    </div>
</section>

<section id="courses" class="relative bg-slate-50 px-4 py-20 sm:px-6 lg:px-8 dark:bg-slate-950">
    <div class="pointer-events-none absolute top-1/3 left-1/2 hidden h-96 w-96 -translate-x-1/2 rounded-full bg-indigo-600/5 blur-3xl dark:block"></div>

    <div class="relative mx-auto max-w-7xl">
        <div class="mb-10">
            <h2 class="text-3xl font-bold text-slate-900 dark:text-slate-100">Сургалтууд</h2>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Хүссэн чиглэлээрээ суралцаж эхлээрэй</p>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($courses as $course)
                @php
                    $stats = $courseStats[$course->id];
                    $badge = match ($course->slug) {
                        'unity' => ['bg' => 'from-fuchsia-500 to-pink-600', 'icon' => '🎮'],
                        'cpp' => ['bg' => 'from-indigo-500 to-blue-600', 'icon' => '⚙️'],
                        'html' => ['bg' => 'from-orange-500 to-red-500', 'icon' => '🌐'],
                        'css' => ['bg' => 'from-cyan-500 to-blue-500', 'icon' => '🎨'],
                        default => ['bg' => 'from-slate-600 to-slate-500', 'icon' => '📘'],
                    };
                    $comingSoon = in_array($course->slug, ['unity'], true);
                @endphp
                <div class="group relative flex flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-lg dark:border-slate-800/80 dark:bg-slate-900/60 dark:shadow-none dark:backdrop-blur-xl dark:hover:border-indigo-500/50 dark:hover:shadow-[0_0_20px_rgba(99,102,241,0.15)] {{ $comingSoon ? 'opacity-75' : '' }}">
                    @if ($comingSoon)
                        <span class="absolute right-4 top-4 flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                            🔒 Тун удахгүй
                        </span>
                    @endif

                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-linear-to-br {{ $badge['bg'] }} text-xl shadow-[0_0_20px_rgba(99,102,241,0.25)] {{ $comingSoon ? 'grayscale' : '' }}">
                        {{ $badge['icon'] }}
                    </div>
                    <h3 class="mt-4 text-lg font-bold text-slate-900 dark:text-slate-100">{{ $course->name }}</h3>
                    <p class="mt-1 flex-1 text-sm text-slate-600 dark:text-slate-400">{{ $course->description }}</p>

                    @unless ($comingSoon)
                        <div class="mt-4">
                            <div class="flex items-center justify-between text-xs font-medium text-slate-500 dark:text-slate-400">
                                <span>Явц</span>
                                <span class="text-slate-800 dark:text-slate-200">{{ $stats['percent'] }}%</span>
                            </div>
                            <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                <div class="h-full rounded-full bg-linear-to-r from-indigo-500 via-purple-500 to-emerald-400" style="width: {{ $stats['percent'] }}%"></div>
                            </div>
                        </div>
                    @endunless

                    @if ($comingSoon)
                        <span class="mt-5 block cursor-not-allowed rounded-lg bg-slate-100 px-4 py-2.5 text-center text-sm font-semibold text-slate-400 dark:bg-slate-800 dark:text-slate-500">
                            🔒 Тун удахгүй
                        </span>
                    @else
                        <a href="{{ route('courses.show', $course) }}"
                           class="mt-5 block rounded-lg bg-linear-to-r from-indigo-500 to-purple-600 px-4 py-2.5 text-center text-sm font-semibold text-white transition-all duration-300 hover:shadow-[0_0_20px_rgba(99,102,241,0.45)]">
                            {{ $stats['completed'] > 0 ? 'Үргэлжлүүлэх' : 'Эхлэх' }}
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
