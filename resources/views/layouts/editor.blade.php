<!DOCTYPE html>
<html lang="mn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Code Editor — ' . config('app.name'))</title>

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
<body class="h-screen overflow-hidden bg-slate-100 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
    @yield('content')

    @stack('scripts')
</body>
</html>
