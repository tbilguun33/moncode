@extends('layouts.app')

@section('title', 'Нэвтрэх — ' . config('app.name'))

@section('content')
<div class="mx-auto flex min-h-[70vh] max-w-md flex-col justify-center px-4 py-12 sm:px-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm dark:border-slate-800/80 dark:bg-slate-900/60 dark:shadow-none dark:backdrop-blur-xl">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Нэвтрэх</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Дахин тавтай морил! Мэдээллээ оруулна уу.</p>

        @if ($errors->any())
            <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300">И-мэйл</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Нууц үг</label>
                <input id="password" type="password" name="password" required
                    class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100">
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                <input type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800">
                Намайг сана
            </label>
            <button type="submit" class="w-full rounded-lg bg-linear-to-r from-indigo-500 to-purple-600 px-4 py-2.5 text-sm font-semibold text-white shadow-[0_0_15px_rgba(99,102,241,0.35)] transition-all duration-300 hover:shadow-[0_0_25px_rgba(99,102,241,0.55)]">
                Нэвтрэх
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
            Бүртгэлгүй юу?
            <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:underline dark:text-indigo-400">Бүртгүүлэх</a>
        </p>
    </div>
</div>
@endsection
