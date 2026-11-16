
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow | Manage Smarter</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800">
    <div class="min-h-screen flex">
        <aside class="w-64 bg-slate-950 text-white p-7 hidden md:block">
            <h1 class="text-2xl font-bold tracking-tight">✦ TaskFlow</h1>
            <p class="text-slate-400 text-xs mt-2">WORKSPACE MANAGEMENT</p>
            <nav class="mt-12 space-y-3">
                <a href="/" class="block rounded-xl bg-violet-600 px-4 py-3 font-medium">▦ Dashboard</a>
                <a href="/" class="block rounded-xl px-4 py-3 text-slate-300 hover:bg-slate-800">✓ My Tasks</a>
            </nav>
            <div class="mt-20 rounded-xl bg-slate-900 p-4">
                <p class="font-semibold">Stay productive!</p>
                <p class="text-xs text-slate-400 mt-2">Small steps make big progress.</p>
            </div>
        </aside>
        <main class="flex-1 p-6 md:p-10">
            @yield('content')
        </main>
    </div>
</body>
</html>
