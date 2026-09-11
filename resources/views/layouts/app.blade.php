<!DOCTYPE html>
<html lang="mn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Moncode'))</title>

    <script>
        (function () {
            var stored = localStorage.getItem('theme');
            var isDark = stored ? stored === 'dark' : true;
            document.documentElement.classList.toggle('dark', isDark);
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="flex min-h-screen flex-col bg-slate-50 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">

    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/80 backdrop-blur-xl dark:border-slate-800/80 dark:bg-slate-950/70">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center gap-8">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 text-xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-linear-to-br from-indigo-500 to-purple-600 font-mono text-sm text-white shadow-[0_0_20px_rgba(99,102,241,0.45)]">&lt;/&gt;</span>
                        {{ config('app.name', 'Moncode') }}
                    </a>
                    <nav class="hidden items-center gap-6 text-sm font-medium text-slate-600 dark:text-slate-400 md:flex">
                        <a href="{{ route('home') }}" class="transition hover:text-slate-900 dark:hover:text-white {{ request()->routeIs('home') ? 'text-slate-900 dark:text-white' : '' }}">Эхлэл</a>

                        <div class="relative" data-dropdown>
                            <button type="button" data-dropdown-toggle
                                    class="flex items-center gap-1 transition hover:text-slate-900 dark:hover:text-white {{ request()->routeIs('courses.*') ? 'text-slate-900 dark:text-white' : '' }}">
                                Сургалтууд
                                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                            </button>
                            <div data-dropdown-menu class="absolute left-0 mt-3 hidden w-48 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-xl dark:border-slate-800 dark:bg-slate-900/95 dark:shadow-2xl dark:backdrop-blur-xl">
                                @foreach ($navCourses as $navCourse)
                                    @php $navLocked = in_array($navCourse->slug, ['unity'], true); @endphp
                                    @if ($navLocked)
                                        <span class="flex cursor-not-allowed items-center justify-between px-4 py-2 text-sm text-slate-400 dark:text-slate-600">
                                            {{ $navCourse->name }}
                                            <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" /></svg>
                                        </span>
                                    @else
                                        <a href="{{ route('courses.show', $navCourse) }}"
                                           class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-white/5 dark:hover:text-white">
                                            {{ $navCourse->name }}
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <a href="{{ route('projects.index') }}" class="transition hover:text-slate-900 dark:hover:text-white {{ request()->routeIs('projects.*') ? 'text-slate-900 dark:text-white' : '' }}">Төслүүд</a>
                    </nav>
                </div>

                <div class="flex items-center gap-1">
                    <button type="button" data-theme-toggle aria-label="Өнгө солих"
                            class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-white">
                        <svg class="h-5 w-5 dark:hidden" viewBox="0 0 24 24" fill="currentColor"><path d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" /></svg>
                        <svg class="hidden h-5 w-5 dark:block" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.25a.75.75 0 01.75.75v2.25a.75.75 0 01-1.5 0V3a.75.75 0 01.75-.75zM7.5 12a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM18.894 6.166a.75.75 0 00-1.06-1.06l-1.591 1.59a.75.75 0 101.06 1.061l1.591-1.59zM21.75 12a.75.75 0 01-.75.75h-2.25a.75.75 0 010-1.5H21a.75.75 0 01.75.75zM17.834 18.894a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 10-1.061 1.06l1.59 1.591zM12 18a.75.75 0 01.75.75V21a.75.75 0 01-1.5 0v-2.25A.75.75 0 0112 18zM7.758 17.303a.75.75 0 00-1.061-1.06l-1.591 1.59a.75.75 0 001.06 1.061l1.591-1.59zM6 12a.75.75 0 01-.75.75H3a.75.75 0 010-1.5h2.25A.75.75 0 016 12zM6.697 7.757a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 00-1.061 1.06l1.59 1.591z" /></svg>
                    </button>

                    @guest
                        <a href="{{ route('login') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-white/5 dark:hover:text-white">
                            Нэвтрэх
                        </a>
                        <a href="{{ route('register') }}"
                           class="rounded-lg bg-linear-to-r from-indigo-500 to-purple-600 px-4 py-2 text-sm font-semibold text-white shadow-[0_0_15px_rgba(99,102,241,0.35)] transition-all duration-300 hover:shadow-[0_0_25px_rgba(99,102,241,0.55)]">
                            Бүртгүүлэх
                        </a>
                    @else
                        <div class="relative" data-dropdown>
                            <button type="button" data-dropdown-toggle class="flex items-center gap-2 rounded-full py-1 pl-1 pr-2 transition hover:bg-slate-100 dark:hover:bg-white/5">
                                @if(auth()->user()->avatar)
                                    <img src="{{ asset('storage/'.auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="h-8 w-8 rounded-full object-cover ring-1 ring-slate-200 dark:ring-slate-700">
                                @else
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-linear-to-br from-indigo-500 to-cyan-500 text-sm font-semibold text-white shadow-[0_0_15px_rgba(99,102,241,0.35)]">
                                        {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                                    </span>
                                @endif
                                <span class="hidden text-sm font-medium text-slate-700 dark:text-slate-200 sm:block">{{ auth()->user()->name }}</span>
                                <svg class="h-4 w-4 text-slate-400 dark:text-slate-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                            </button>
                            <div data-dropdown-menu class="absolute right-0 mt-2 hidden w-48 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-xl dark:border-slate-800 dark:bg-slate-900/95 dark:shadow-2xl dark:backdrop-blur-xl">
                                <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-white/5 dark:hover:text-white">Профайл</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-slate-50 dark:text-red-400 dark:hover:bg-white/5 dark:hover:text-red-300">Гарах</button>
                                </form>
                            </div>
                        </div>
                    @endguest

                    <button type="button" data-mobile-toggle class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-white/5 md:hidden" aria-label="Цэс">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" /></svg>
                    </button>
                </div>
            </div>

            <nav data-mobile-menu class="hidden flex-col gap-1 pb-4 md:hidden">
                <a href="{{ route('home') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-white/5 dark:hover:text-white">Эхлэл</a>

                <p class="px-3 pt-2 text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Сургалтууд</p>
                @foreach ($navCourses as $navCourse)
                    @php $navLocked = in_array($navCourse->slug, ['unity'], true); @endphp
                    @if ($navLocked)
                        <span class="flex cursor-not-allowed items-center justify-between rounded-lg px-6 py-2 text-sm font-medium text-slate-400 dark:text-slate-600">
                            {{ $navCourse->name }}
                            <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" /></svg>
                        </span>
                    @else
                        <a href="{{ route('courses.show', $navCourse) }}" class="rounded-lg px-6 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-white/5 dark:hover:text-white">
                            {{ $navCourse->name }}
                        </a>
                    @endif
                @endforeach

                <a href="{{ route('projects.index') }}" class="mt-1 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-white/5 dark:hover:text-white">Төслүүд</a>
            </nav>
        </div>
    </header>

    @if (session('status'))
        <div class="mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
            <div class="rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300 dark:shadow-[0_0_20px_rgba(16,185,129,0.15)]">
                {{ session('status') }}
            </div>
        </div>
    @endif

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="mt-16 border-t border-slate-200 bg-white dark:border-slate-800/80 dark:bg-slate-950">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-2 px-4 py-6 text-sm text-slate-500 sm:flex-row sm:px-6 lg:px-8">
            <span>&copy; {{ date('Y') }} {{ config('app.name', 'Moncode') }}</span>
            <span>Zero to Hero — Сур, Турш, Хөгж.</span>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
