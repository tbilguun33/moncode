@extends('layouts.app')

@section('title', $lesson->title . ' — ' . config('app.name'))

@php use App\Enums\EditorType; @endphp

@if ($isTextBased)
    @push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css">
    @endpush
@endif

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="lg:flex lg:items-start lg:gap-8">

        {{-- Mobile sidebar toggle --}}
        <div class="py-4 lg:hidden">
            <button type="button" data-sidebar-toggle
                    class="flex w-full items-center justify-between rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm dark:border-slate-800 dark:bg-slate-900/60 dark:text-slate-200">
                <span>{{ $course->name }} — Агуулга</span>
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
            </button>
        </div>

        {{-- Sidebar: full course table of contents --}}
        <aside data-sidebar class="hidden pb-8 lg:block lg:w-64 lg:shrink-0">
            <div class="rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-800 dark:bg-slate-900/60 lg:sticky lg:top-20 lg:max-h-[calc(100vh-6rem)] lg:overflow-y-auto">
                <p class="px-3 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">{{ strtoupper($course->name) }} TUTORIAL</p>

                <a href="{{ route('courses.show', $course) }}"
                   class="mt-2 block rounded-lg border-l-4 border-transparent px-3 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-white/5">
                    {{ strtoupper($course->name) }} HOME
                </a>

                <nav class="mt-3 space-y-1">
                    @foreach ($categories as $category)
                        @php $categoryIsActive = $category->lessons->contains('id', $lesson->id); @endphp
                        @if ($category->lessons->count() === 1)
                            @php $singleLesson = $category->lessons->first(); $isActive = $singleLesson->id === $lesson->id; @endphp
                            <a href="{{ route('lessons.show', $singleLesson) }}"
                               class="block truncate rounded-lg border-l-4 px-3 py-2 text-sm transition
                                   {{ $isActive
                                       ? 'border-emerald-500 bg-slate-100 font-bold text-slate-900 dark:bg-white/10 dark:text-white'
                                       : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-white' }}">
                                {{ $category->name }}
                            </a>
                        @else
                            <div data-category-dropdown>
                                <button type="button" data-category-toggle
                                        class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-400 transition hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300">
                                    <span>{{ $category->name }}</span>
                                    <svg class="h-3.5 w-3.5 shrink-0 transition-transform {{ $categoryIsActive ? 'rotate-180' : '' }}" data-category-chevron viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                                </button>
                                <div data-category-panel class="mt-1 space-y-0.5 {{ $categoryIsActive ? '' : 'hidden' }}">
                                    @foreach ($category->lessons as $navLesson)
                                        @php $isActive = $navLesson->id === $lesson->id; @endphp
                                        <a href="{{ route('lessons.show', $navLesson) }}"
                                           class="block truncate rounded-lg border-l-4 px-3 py-2 text-sm transition
                                               {{ $isActive
                                                   ? 'border-emerald-500 bg-slate-100 font-bold text-slate-900 dark:bg-white/10 dark:text-white'
                                                   : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-white' }}">
                                            {{ $navLesson->title }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </nav>
            </div>
        </aside>

        {{-- Main content --}}
        <div class="min-w-0 flex-1 py-6 lg:py-10">
            <nav class="mb-6 flex flex-wrap items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                <a href="{{ route('home') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Эхлэл</a>
                <span>/</span>
                <a href="{{ route('courses.show', $course) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">{{ $course->name }}</a>
                <span>/</span>
                <span class="font-medium text-slate-700 dark:text-slate-200">{{ $lesson->title }}</span>
            </nav>

            <h1 class="text-3xl font-bold text-slate-900 sm:text-4xl dark:text-slate-100">{{ $lesson->title }}</h1>

            @if ($isTextBased)
                {{-- Explanation (trusted HTML content authored via the seeder/admin) --}}
                <div class="prose prose-slate mt-6 max-w-none dark:prose-invert prose-headings:font-bold prose-a:text-indigo-600 prose-code:before:content-none prose-code:after:content-none dark:prose-a:text-indigo-400">
                    {!! $lesson->content !!}
                </div>

                {{-- Example box, W3Schools-style --}}
                @if ($lesson->starter_code)
                    @php $language = $lesson->category->course->editor_type === EditorType::Cpp ? 'cpp' : 'markup'; @endphp
                    <div class="my-6 rounded border border-gray-200 bg-gray-100 p-6 dark:border-slate-800 dark:bg-slate-900/60">
                        <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">Жишээ</h2>

                        <div data-code-block class="relative mt-4 overflow-hidden rounded border-l-4 border-emerald-500 bg-white dark:bg-[#1e1e2e]">
                            <button type="button" data-copy-code
                                    class="absolute right-2 top-2 rounded bg-white/80 px-2 py-1 text-xs font-medium text-slate-500 shadow-sm transition hover:text-slate-900 dark:bg-slate-800/80 dark:text-slate-400 dark:hover:text-white">
                                Хуулах
                            </button>
                            <pre class="m-0 overflow-x-auto p-4 text-sm leading-6"><code class="language-{{ $language }}" data-copy-target>{{ $lesson->starter_code }}</code></pre>
                        </div>

                        <a href="{{ route('lessons.editor', $lesson) }}"
                           class="mt-4 inline-flex items-center gap-1.5 rounded bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-700">
                            Туршиж үзэх »
                        </a>
                    </div>
                @endif

                <div class="mt-2">
                    @auth
                        @if ($isCompleted)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                                ✓ Дуусгасан
                            </span>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-500 transition hover:text-indigo-600 dark:bg-slate-800 dark:text-slate-400 dark:hover:text-indigo-400">
                            Явцаа хадгалахын тулд нэвтэрнэ үү
                        </a>
                    @endauth
                </div>
            @else
                {{-- Video-based lesson (Unity): watch-through tracked via YouTube IFrame API --}}
                @if ($lesson->video_url)
                    @php
                        $embedUrl = $lesson->video_url
                            . (str_contains($lesson->video_url, '?') ? '&' : '?')
                            . 'enablejsapi=1&origin=' . urlencode(request()->getSchemeAndHttpHost());
                    @endphp
                    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-black dark:border-slate-800">
                        <div class="aspect-video w-full resize-y overflow-auto" style="min-height: 220px">
                            <iframe id="yt-player" src="{{ $embedUrl }}" class="h-full w-full" style="min-height: 220px" title="{{ $lesson->title }}"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        </div>
                    </div>
                    <p class="mt-1 text-right text-xs text-slate-400 dark:text-slate-500">↘ Видео цонхны хэмжээг чирж томруулах/жижигрүүлэх боломжтой</p>

                    <div class="mt-4">
                        @auth
                            <span data-completion-badge
                                  class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold
                                      {{ $isCompleted
                                          ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'
                                          : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                                {{ $isCompleted ? '✓ Дуусгасан' : '▶ Видеог бүрэн үзвэл автоматаар тэмдэглэгдэнэ' }}
                            </span>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-500 transition hover:text-indigo-600 dark:bg-slate-800 dark:text-slate-400 dark:hover:text-indigo-400">
                                Явцаа хадгалахын тулд нэвтэрнэ үү
                            </a>
                        @endauth
                    </div>
                @endif

                <div class="mt-8 text-slate-700 dark:text-slate-300">
                    <p class="whitespace-pre-line text-base leading-relaxed">{{ $lesson->content }}</p>
                </div>
            @endif

            {{-- Bottom navigation bar, W3Schools-style green Previous/Next --}}
            <div class="mt-10 flex items-stretch gap-0.5 overflow-hidden rounded">
                @if ($previousLesson)
                    <a href="{{ route('lessons.show', $previousLesson) }}"
                       class="flex flex-1 items-center justify-center gap-1.5 bg-emerald-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-emerald-700 sm:flex-none sm:px-8">
                        ❮ Өмнөх
                    </a>
                @endif
                @if ($nextLesson)
                    <a href="{{ route('lessons.show', $nextLesson) }}"
                       class="flex flex-1 items-center justify-center gap-1.5 bg-emerald-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-emerald-700 sm:flex-none sm:px-8">
                        Дараах ❯
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@if ($isTextBased)
    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-clike.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-markup.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-c.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-cpp.min.js"></script>
    <script>Prism.highlightAll();</script>
    @endpush
@else
    @auth
        @if ($lesson->video_url)
            @push('scripts')
            <script src="https://www.youtube.com/iframe_api"></script>
            <script>
                (function () {
                    var alreadyCompleted = {{ $isCompleted ? 'true' : 'false' }};
                    var markCompleteUrl = @json(route('lessons.mark-complete', $lesson));

                    function markComplete() {
                        if (alreadyCompleted) return;
                        alreadyCompleted = true;

                        fetch(markCompleteUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                        }).then(function () {
                            var badge = document.querySelector('[data-completion-badge]');
                            if (badge) {
                                badge.className = 'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300';
                                badge.textContent = '✓ Дуусгасан';
                            }
                        });
                    }

                    window.onYouTubeIframeAPIReady = function () {
                        new YT.Player('yt-player', {
                            events: {
                                onStateChange: function (event) {
                                    if (event.data === YT.PlayerState.ENDED) {
                                        markComplete();
                                    }
                                },
                            },
                        });
                    };
                })();
            </script>
            @endpush
        @endif
    @endauth
@endif
