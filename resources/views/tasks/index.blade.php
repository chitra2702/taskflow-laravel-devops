
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex flex-wrap justify-between items-center gap-4 mb-9">
        <div>
            <p class="text-sm text-violet-600 font-semibold">OVERVIEW</p>
            <h2 class="text-3xl font-bold mt-1">My Dashboard</h2>
            <p class="text-slate-500 mt-2">Manage your work and stay on track.</p>
        </div>
        <span class="bg-white border rounded-xl px-4 py-3 text-sm text-slate-500">
            TaskFlow Workspace
        </span>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-4 rounded-xl mb-6">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-9">
        @foreach([
            ['Total Tasks', $total, '📋'],
            ['Pending', $pending, '◷'],
            ['In Progress', $progress, '↗'],
            ['Completed', $completed, '✓']
        ] as [$label, $count, $icon])
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
            <div class="flex justify-between">
                <p class="text-slate-500 text-sm">{{ $label }}</p>
                <span class="text-xl">{{ $icon }}</span>
            </div>
            <p class="text-3xl font-bold mt-4">{{ $count }}</p>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <section class="xl:col-span-1 bg-white rounded-2xl border p-6 shadow-sm">
            <h3 class="text-lg font-bold mb-5">Create New Task</h3>
            <form action="{{ route('tasks.store') }}" method="POST" class="space-y-4">
                @csrf
                <input name="title" required placeholder="Task title"
                    class="w-full border rounded-xl p-3 focus:ring-2 focus:ring-violet-400 outline-none">
                <textarea name="description" placeholder="Description"
                    class="w-full border rounded-xl p-3 h-24 outline-none focus:ring-2 focus:ring-violet-400"></textarea>
                <select name="priority" class="w-full border rounded-xl p-3 bg-white">
                    <option>Medium</option>
                    <option>High</option>
                    <option>Low</option>
                </select>
                <input type="date" name="due_date" class="w-full border rounded-xl p-3">
                <button class="w-full bg-violet-600 hover:bg-violet-700 text-white rounded-xl p-3 font-semibold">
                    + Add Task
                </button>
            </form>
        </section>

        <section class="xl:col-span-2 bg-white rounded-2xl border p-6 shadow-sm">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-lg font-bold">Recent Tasks</h3>
                <span class="text-sm text-slate-400">{{ $total }} tasks</span>
            </div>

            <div class="space-y-3">
                @forelse($tasks as $task)
                <div class="border rounded-xl p-4">
                    <div class="flex flex-wrap justify-between gap-3">
                        <div>
                            <h4 class="font-semibold">{{ $task->title }}</h4>
                            <p class="text-sm text-slate-500 mt-1">{{ $task->description }}</p>
                            <p class="text-xs text-slate-400 mt-2">
                                {{ $task->priority }} priority
                                @if($task->due_date)
                                    · Due {{ $task->due_date }}
                                @endif
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <form action="{{ route('tasks.update', $task) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()"
                                    class="text-xs border rounded-lg p-2 bg-violet-50">
                                    @foreach(['Pending', 'In Progress', 'Completed'] as $status)
                                        <option value="{{ $status }}" @selected($task->status === $status)>
                                            {{ $status }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                            <form action="{{ route('tasks.destroy', $task) }}" method="POST"
                                onsubmit="return confirm('Delete this task?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-500 text-sm hover:text-red-700">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                    <div class="text-center py-14 text-slate-400">
                        <p class="text-3xl mb-3">🗂️</p>
                        <p>No tasks yet. Create your first task!</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
