@extends('layouts.app')

@section('title', 'Профайл — ' . config('app.name'))

@section('content')
<div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm dark:border-slate-800/80 dark:bg-slate-900/60 dark:shadow-none dark:backdrop-blur-xl">
        <div class="flex items-center gap-4">
            @if(auth()->user()->avatar)
                <img src="{{ asset('storage/'.auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="h-16 w-16 rounded-full object-cover">
            @else
                <span class="flex h-16 w-16 items-center justify-center rounded-full bg-linear-to-br from-indigo-500 to-cyan-500 text-xl font-semibold text-white shadow-[0_0_20px_rgba(99,102,241,0.35)]">
                    {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                </span>
            @endif
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ auth()->user()->name }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ auth()->user()->email }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-500">{{ auth()->user()->created_at->translatedFormat('Y-m-d') }}-нээс хойш гишүүн</p>
            </div>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-xl bg-slate-50 p-4 text-center dark:bg-slate-800/60">
                <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $completed }}/{{ $totalLessons }}</p>
                <p class="mt-1 text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Дуусгасан хичээл</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-4 text-center dark:bg-slate-800/60">
                <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $percent }}%</p>
                <p class="mt-1 text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Нийт явц</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-4 text-center dark:bg-slate-800/60">
                <p class="text-2xl font-bold text-amber-500 dark:text-amber-400">{{ $projectsCount }}</p>
                <p class="mt-1 text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Нийтэлсэн төсөл</p>
            </div>
        </div>

        <div class="mt-6 h-2.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
            <div class="h-full rounded-full bg-linear-to-r from-indigo-500 via-purple-500 to-emerald-400" style="width: {{ $percent }}%"></div>
        </div>
    </div>
</div>
@endsection
