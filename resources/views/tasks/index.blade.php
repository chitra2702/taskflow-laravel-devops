@extends('layouts.app')

@section('content')
@php
    use Carbon\Carbon;

    $userName = optional(auth()->user())->name ?? 'Guest';
    $initial  = strtoupper(mb_substr($userName, 0, 1));

    // Pastel themes (card gradient, tag chip, progress bar colour)
    $themes = [
        'pink'   => ['card' => 'from-rose-100 to-pink-100',      'bar' => 'bg-rose-400',    'tag' => 'text-rose-500'],
        'orange' => ['card' => 'from-orange-100 to-amber-100',   'bar' => 'bg-orange-400',  'tag' => 'text-orange-500'],
        'blue'   => ['card' => 'from-indigo-100 to-blue-100',    'bar' => 'bg-indigo-500',  'tag' => 'text-indigo-500'],
        'green'  => ['card' => 'from-emerald-100 to-teal-100',   'bar' => 'bg-emerald-500', 'tag' => 'text-emerald-600'],
    ];

    $themeFor = function ($task) {
        if ($task->status === 'Completed') return 'green';
        return match ($task->priority) {
            'High'   => 'pink',
            'Medium' => 'orange',
            default  => 'blue',
        };
    };

    $progressFor = fn ($task) => match ($task->status) {
        'Completed'   => 100,
        'In Progress' => 50,
        default       => 0,
    };

    $daysLabel = function ($task) {
        if (!$task->due_date) return 'No deadline';
        $d = (int) now()->startOfDay()->diffInDays(Carbon::parse($task->due_date)->startOfDay(), false);
        if ($d < 0)  return abs($d) . 'd overdue';
        if ($d === 0) return 'Today';
        return $d . ($d === 1 ? ' day' : ' days');
    };

    $columns = [
        'Pending'     => ['title' => 'Todo List',   'dot' => 'bg-rose-400'],
        'In Progress' => ['title' => 'In Progress', 'dot' => 'bg-indigo-400'],
        'Completed'   => ['title' => 'Completed',   'dot' => 'bg-emerald-400'],
    ];

    $timeLimit = $tasks->filter(fn ($t) => $t->due_date && $t->status !== 'Completed')
                       ->sortBy('due_date')->take(4)->values();

    $percent = $total > 0 ? round(($completed / $total) * 100) : 0;
@endphp

<div class="relative h-screen overflow-hidden p-3 md:p-6">

    {{-- Decorative blobs --}}
    <div class="pointer-events-none absolute -top-24 -left-24 w-96 h-96 rounded-full bg-fuchsia-300/60 blur-3xl"></div>
    <div class="pointer-events-none absolute top-1/4 -right-24 w-96 h-96 rounded-full bg-violet-400/50 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-32 left-1/3 w-[28rem] h-[28rem] rounded-full bg-rose-300/60 blur-3xl"></div>
    <div class="pointer-events-none absolute -top-8 right-24 w-16 h-16 rounded-full bg-gradient-to-br from-rose-400 to-red-400"></div>
    <div class="pointer-events-none absolute top-1/3 -right-8 w-28 h-28 rounded-full bg-gradient-to-br from-violet-500 to-indigo-600"></div>
    <div class="pointer-events-none absolute -bottom-10 -left-6 w-24 h-24 rounded-full bg-gradient-to-br from-rose-400 to-pink-400"></div>

    <div class="relative flex h-full rounded-[28px] bg-white/45 backdrop-blur-2xl shadow-2xl shadow-violet-300/50 ring-1 ring-white/70 overflow-hidden">

        {{-- ============ SIDEBAR ============ --}}
        <aside class="hidden lg:flex w-64 shrink-0 flex-col border-r border-slate-100 bg-gradient-to-b from-white/50 to-violet-100/30 p-5">
            <div class="flex items-center gap-2 px-2 mb-8">
                <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-rose-500 to-pink-500 text-white flex items-center justify-center shadow-md shadow-rose-200">✓</span>
                <span class="text-xl font-extrabold tracking-tight text-slate-900">TaskTrack</span>
            </div>

            <nav class="space-y-1 text-sm">
                <a href="/" class="flex items-center justify-between rounded-xl bg-white shadow-sm ring-1 ring-slate-100 px-3 py-2.5 font-semibold text-slate-900">
                    <span class="flex items-center gap-3"><span class="text-rose-500">◎</span> Task</span>
                    <span class="text-xs font-bold text-rose-500">{{ $total }}</span>
                </a>
                <a href="#" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-slate-500 hover:bg-white/70 transition"><span>↗</span> Activity</a>
            </nav>

            <p class="mt-8 mb-2 px-3 text-[11px] font-semibold tracking-widest text-slate-400">MAIN</p>
            <nav class="space-y-1 text-sm text-slate-500">
                <a href="/" class="flex items-center gap-3 rounded-xl px-3 py-2.5 hover:bg-white/70 transition"><span>▦</span> Dashboard</a>
                <a href="#" class="flex items-center gap-3 rounded-xl px-3 py-2.5 hover:bg-white/70 transition"><span>📅</span> Schedule</a>
                <a href="#" class="flex items-center gap-3 rounded-xl px-3 py-2.5 hover:bg-white/70 transition"><span>🗒</span> Note</a>
                <a href="#" class="flex items-center gap-3 rounded-xl px-3 py-2.5 hover:bg-white/70 transition"><span>📊</span> Report</a>
            </nav>

            <p class="mt-8 mb-2 px-3 text-[11px] font-semibold tracking-widest text-slate-400">REPORT</p>
            <nav class="space-y-1 text-sm text-slate-500">
                <a href="#" class="flex items-center gap-3 rounded-xl px-3 py-2.5 hover:bg-white/70 transition"><span>👥</span> Team</a>
                <a href="#" class="flex items-center gap-3 rounded-xl px-3 py-2.5 hover:bg-white/70 transition"><span>◔</span> Client</a>
            </nav>

            <nav class="mt-6 space-y-1 text-sm text-slate-500">
                <a href="#" class="flex items-center gap-3 rounded-xl px-3 py-2.5 hover:bg-white/70 transition"><span>⚙</span> Setting</a>
                <a href="#" class="flex items-center gap-3 rounded-xl px-3 py-2.5 hover:bg-white/70 transition"><span>🎧</span> Support</a>
            </nav>

            <div class="mt-auto flex items-center gap-3 rounded-2xl bg-white/80 ring-1 ring-slate-100 p-3">
                <span class="w-9 h-9 rounded-full bg-gradient-to-br from-violet-500 to-indigo-500 text-white font-bold flex items-center justify-center">{{ $initial }}</span>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-900 truncate">{{ $userName }}</p>
                    <p class="text-xs text-slate-400 truncate">{{ optional(auth()->user())->email ?? 'TaskFlow Workspace' }}</p>
                </div>
            </div>
        </aside>

        {{-- ============ MAIN ============ --}}
        <main class="flex-1 min-w-0 overflow-y-auto p-5 md:p-7">

            {{-- Top bar --}}
            <header class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <div>
                    <p class="text-xs text-slate-500">Welcome Back</p>
                    <p class="font-bold text-slate-900">{{ $userName }}</p>
                </div>

                <form action="/" method="GET" class="order-3 md:order-none w-full md:w-auto md:flex-1 md:max-w-md">
                    <div class="flex items-center gap-3 bg-white rounded-2xl shadow-lg shadow-violet-100 ring-1 ring-slate-100 px-4 py-3">
                        <span class="text-slate-400">🔍</span>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search Task"
                            class="flex-1 bg-transparent outline-none text-sm placeholder:text-slate-400">
                        @if($search)
                            <a href="/" class="text-xs text-slate-400 hover:text-slate-700">Clear</a>
                        @endif
                        <kbd class="text-[11px] font-semibold text-slate-400 bg-slate-100 rounded-md px-1.5 py-0.5">⌘ K</kbd>
                    </div>
                </form>

                <div class="flex items-center gap-3">
                    <span class="relative w-10 h-10 rounded-full bg-white ring-1 ring-slate-100 shadow-sm flex items-center justify-center">
                        🔔
                        <span class="absolute top-2 right-2.5 w-2 h-2 rounded-full bg-rose-500"></span>
                    </span>
                    <span class="w-10 h-10 rounded-full bg-gradient-to-br from-violet-500 to-indigo-500 text-white font-bold flex items-center justify-center ring-2 ring-white shadow">{{ $initial }}</span>
                </div>
            </header>

            @if(session('success'))
                <div class="flex items-center gap-3 bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 px-5 py-3 rounded-2xl mb-5 text-sm">
                    <span class="w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px]">✓</span>
                    {{ session('success') }}
                </div>
            @endif

            {{-- Board header panel --}}
            <section class="flex flex-wrap items-center justify-between gap-5 rounded-2xl bg-white/60 ring-1 ring-white/70 px-5 py-4 mb-7">
                <div>
                    <h2 class="text-xl font-extrabold text-slate-900">{{ now()->format('F') }}</h2>
                    <p class="text-xs text-slate-500">Today is {{ now()->format('l, M jS, Y') }}</p>
                </div>

                <div class="flex items-center gap-2 text-sm">
                    <span class="font-bold text-slate-900">Board</span>
                    <span class="text-slate-300">—</span>
                    <span class="text-slate-500">Daily Task ⌄</span>
                </div>

                <div class="hidden md:flex items-center gap-3">
                    <div class="flex -space-x-2">
                        @foreach(['from-rose-400 to-pink-500', 'from-sky-400 to-indigo-500', 'from-emerald-400 to-teal-500'] as $g)
                            <span class="w-8 h-8 rounded-full bg-gradient-to-br {{ $g }} ring-2 ring-white"></span>
                        @endforeach
                        <span class="w-8 h-8 rounded-full bg-white ring-2 ring-white text-[11px] font-semibold text-slate-600 flex items-center justify-center shadow-sm">+{{ max($total, 1) }}</span>
                    </div>
                </div>

                <div class="hidden md:flex items-center gap-5 text-sm text-slate-600">
                    <span class="flex items-center gap-2 rounded-xl bg-white ring-1 ring-slate-100 px-3 py-2">▦ Category</span>
                    <span class="flex items-center gap-2">☰ Sort By: Deadline</span>
                </div>

                <button type="button" onclick="openTaskModal()"
                    class="bg-gradient-to-r from-rose-500 to-pink-500 hover:from-rose-600 hover:to-pink-600 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-lg shadow-rose-200 transition">
                    + New Task
                </button>
            </section>

            {{-- Stat chips --}}
            <div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
                @foreach([
                    ['Total',       $total,     'from-violet-500 to-indigo-500'],
                    ['Pending',     $pending,   'from-rose-400 to-pink-500'],
                    ['In Progress', $progress,  'from-sky-400 to-indigo-500'],
                    ['Completed',   $completed, 'from-emerald-400 to-teal-500'],
                ] as [$label, $count, $g])
                    <div class="flex items-center gap-4 rounded-2xl bg-white/80 ring-1 ring-slate-100 px-4 py-3">
                        <span class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $g }} text-white font-bold flex items-center justify-center shadow">{{ $count }}</span>
                        <p class="text-sm font-medium text-slate-600">{{ $label }}</p>
                    </div>
                @endforeach
            </div>

            {{-- ============ TIME LIMIT ============ --}}
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-extrabold text-slate-900">Time Limit</h3>
                <span class="text-xs text-slate-400">Closest deadlines</span>
            </div>

            @if($timeLimit->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-200 text-center text-sm text-slate-400 py-8 mb-9">
                    No upcoming deadlines. Add a due date to a task to see it here.
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-9">
                    @foreach($timeLimit as $task)
                        @php $key = $themeFor($task); $t = $themes[$key]; $pct = $progressFor($task); @endphp
                        <article class="relative overflow-hidden rounded-2xl bg-gradient-to-br {{ $t['card'] }} p-4 hover:-translate-y-0.5 hover:shadow-lg transition">
                            <div class="absolute -right-8 -top-8 w-28 h-28 rounded-full bg-white/30"></div>
                            <div class="relative">
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="text-[11px] font-semibold bg-white/70 {{ $t['tag'] }} rounded-full px-2.5 py-0.5">#{{ strtolower($task->priority) }}</span>
                                    <span class="text-[11px] font-semibold bg-white/70 text-slate-500 rounded-full px-2.5 py-0.5">#{{ strtolower(str_replace(' ', '', $task->status)) }}</span>
                                </div>
                                <h4 class="font-bold text-slate-900 leading-snug line-clamp-2 min-h-[2.5rem]">{{ $task->title }}</h4>

                                <div class="flex items-center justify-between mt-4 text-xs text-slate-500">
                                    <span>Progress</span><span class="font-bold text-slate-700">{{ $pct }}%</span>
                                </div>
                                <div class="flex gap-1 mt-1.5">
                                    @for($i = 1; $i <= 10; $i++)
                                        <span class="h-1.5 flex-1 rounded-full {{ $i <= $pct / 10 ? $t['bar'] : 'bg-white/70' }}"></span>
                                    @endfor
                                </div>

                                <div class="flex items-center justify-between mt-4 text-xs text-slate-500">
                                    <span class="w-6 h-6 rounded-full bg-white/80 text-slate-600 font-bold flex items-center justify-center text-[10px]">{{ $initial }}</span>
                                    <span class="flex items-center gap-1">📅 {{ Carbon::parse($task->due_date)->format('M d') }}</span>
                                    <span class="font-semibold">{{ $daysLabel($task) }}</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif

            {{-- ============ ALL TASK ============ --}}
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-extrabold text-slate-900">All Task</h3>
                <span class="text-xs font-semibold bg-slate-100 text-slate-500 px-2.5 py-1 rounded-full">{{ $percent }}% complete</span>
            </div>

            @if($tasks->isEmpty())
                <div class="rounded-3xl border border-dashed border-slate-300 text-center py-16 text-slate-400 bg-white/50">
                    <p class="text-5xl mb-3">🗂️</p>
                    @if($search)
                        <p class="font-medium text-slate-600">No tasks found matching "{{ $search }}"</p>
                    @else
                        <p class="font-medium text-slate-600">No tasks yet</p>
                        <p class="text-sm mt-1">Create your first task to get started.</p>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 pb-4">
                    @foreach($columns as $status => $col)
                        @php $items = $tasks->where('status', $status); @endphp

                        <section class="rounded-3xl bg-white/40 ring-1 ring-white/60 p-4">
                            <div class="flex items-center justify-between mb-4 px-1">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-5 h-5 rounded-full bg-rose-100 flex items-center justify-center">
                                        <span class="w-2 h-2 rounded-full {{ $col['dot'] }}"></span>
                                    </span>
                                    <h4 class="font-bold text-slate-900">{{ $col['title'] }}</h4>
                                    <span class="text-xs font-semibold text-slate-400">{{ $items->count() }}</span>
                                </div>
                                <div class="flex items-center gap-3 text-slate-400">
                                    <button type="button" onclick="openTaskModal()" class="text-lg leading-none hover:text-slate-700">+</button>
                                    <span class="leading-none">⋮</span>
                                </div>
                            </div>

                            <div class="space-y-3">
                                @forelse($items as $task)
                                    @php
                                        $key = $themeFor($task); $t = $themes[$key]; $pct = $progressFor($task);
                                        $overdue = $task->due_date && $task->status !== 'Completed'
                                                   && Carbon::parse($task->due_date)->isPast()
                                                   && !Carbon::parse($task->due_date)->isToday();
                                    @endphp

                                    <article class="relative overflow-hidden rounded-2xl bg-gradient-to-br {{ $t['card'] }} p-4 hover:-translate-y-0.5 hover:shadow-lg transition">
                                        <div class="absolute -right-10 -top-10 w-32 h-32 rounded-full bg-white/30"></div>

                                        <div class="relative">
                                            <div class="flex items-center justify-between mb-3">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-[11px] font-semibold bg-white/70 {{ $t['tag'] }} rounded-full px-2.5 py-0.5">#{{ strtolower($task->priority) }}</span>
                                                    @if($overdue)
                                                        <span class="text-[11px] font-semibold bg-white/70 text-rose-600 rounded-full px-2.5 py-0.5">#overdue</span>
                                                    @endif
                                                </div>

                                                {{-- Delete --}}
                                                <form action="{{ route('tasks.destroy', $task) }}" method="POST"
                                                      onsubmit="return confirm('Delete this task?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button title="Delete"
                                                        class="w-7 h-7 rounded-lg bg-white/60 hover:bg-white text-slate-500 hover:text-rose-600 text-xs transition">🗑</button>
                                                </form>
                                            </div>

                                            <h4 class="font-bold text-slate-900 leading-snug {{ $task->status === 'Completed' ? 'line-through opacity-60' : '' }}">
                                                {{ $task->title }}
                                            </h4>

                                            @if($task->description)
                                                <p class="text-xs text-slate-600 mt-2 line-clamp-2">
                                                    <span class="font-semibold">Note:</span> {{ $task->description }}
                                                </p>
                                            @endif

                                            <div class="flex items-center justify-between mt-4 text-xs text-slate-500">
                                                <span>Progress</span><span class="font-bold text-slate-700">{{ $pct }}%</span>
                                            </div>
                                            <div class="flex gap-1 mt-1.5">
                                                @for($i = 1; $i <= 10; $i++)
                                                    <span class="h-1.5 flex-1 rounded-full {{ $i <= $pct / 10 ? $t['bar'] : 'bg-white/70' }}"></span>
                                                @endfor
                                            </div>

                                            <div class="flex items-center justify-between gap-2 mt-4">
                                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                                    <span class="w-6 h-6 rounded-full bg-white/80 text-slate-600 font-bold flex items-center justify-center text-[10px]">{{ $initial }}</span>
                                                    <span>📅 {{ $task->due_date ? Carbon::parse($task->due_date)->format('M d') : '—' }}</span>
                                                    <span class="font-semibold">{{ $daysLabel($task) }}</span>
                                                </div>

                                                {{-- Status change --}}
                                                <form action="{{ route('tasks.update', $task) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <select name="status" onchange="this.form.submit()"
                                                        class="text-[11px] font-semibold bg-white/70 hover:bg-white border-0 rounded-lg px-2 py-1.5 outline-none focus:ring-2 focus:ring-white cursor-pointer text-slate-600">
                                                        @foreach(['Pending', 'In Progress', 'Completed'] as $s)
                                                            <option value="{{ $s }}" @selected($task->status === $s)>{{ $s }}</option>
                                                        @endforeach
                                                    </select>
                                                </form>
                                            </div>
                                        </div>
                                    </article>
                                @empty
                                    <div class="text-center text-sm text-slate-400 py-10 border border-dashed border-slate-200 rounded-2xl bg-white/40">
                                        Nothing here
                                    </div>
                                @endforelse
                            </div>
                        </section>
                    @endforeach
                </div>
            @endif
        </main>
    </div>
</div>

{{-- ============ CREATE TASK MODAL ============ --}}
<div id="taskModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeTaskModal()"></div>

    <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl p-7">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-xl font-extrabold text-slate-900">Create New Task</h3>
                <p class="text-sm text-slate-500 mt-1">Add something to your board.</p>
            </div>
            <button type="button" onclick="closeTaskModal()"
                class="w-9 h-9 rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-700 transition">✕</button>
        </div>

        <form action="{{ route('tasks.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Title</label>
                <input name="title" required placeholder="e.g. Hotel Booking Dribbble Team"
                    class="mt-1.5 w-full border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-rose-300 focus:border-transparent transition">
            </div>

            <div>
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Description</label>
                <textarea name="description" placeholder="Add a note..."
                    class="mt-1.5 w-full border border-slate-200 rounded-xl px-4 py-3 h-24 outline-none focus:ring-2 focus:ring-rose-300 focus:border-transparent transition"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Priority</label>
                    <select name="priority" class="mt-1.5 w-full border border-slate-200 rounded-xl px-4 py-3 bg-white outline-none focus:ring-2 focus:ring-rose-300">
                        <option>Medium</option>
                        <option>High</option>
                        <option>Low</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Due date</label>
                    <input type="date" name="due_date"
                        class="mt-1.5 w-full border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-rose-300">
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeTaskModal()"
                    class="flex-1 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-xl py-3 font-semibold transition">
                    Cancel
                </button>
                <button class="flex-1 bg-gradient-to-r from-rose-500 to-pink-500 hover:from-rose-600 hover:to-pink-600 text-white rounded-xl py-3 font-semibold shadow-lg shadow-rose-200 transition">
                    + Add Task
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const taskModal = document.getElementById('taskModal');

    function openTaskModal() {
        taskModal.classList.remove('hidden');
        taskModal.classList.add('flex');
        taskModal.querySelector('input[name="title"]').focus();
    }

    function closeTaskModal() {
        taskModal.classList.add('hidden');
        taskModal.classList.remove('flex');
    }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeTaskModal();
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            document.querySelector('input[name="search"]').focus();
        }
    });
</script>
@endsection
