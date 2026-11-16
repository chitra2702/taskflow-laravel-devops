<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $tasks = Task::query()
            ->when($search, function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            })
            ->latest()
            ->get();

        return view('tasks.index', [
            'tasks' => $tasks,
            'search' => $search,
            'total' => Task::count(),
            'pending' => Task::where('status', 'Pending')->count(),
            'progress' => Task::where('status', 'In Progress')->count(),
            'completed' => Task::where('status', 'Completed')->count(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|max:255',
            'description' => 'nullable',
            'priority' => 'required|in:Low,Medium,High',
            'due_date' => 'nullable|date',
        ]);

        Task::create($data);

        return redirect('/')->with('success', 'Task created successfully!');
    }

    public function update(Request $request, Task $task)
    {
        $request->validate([
            'status' => 'required|in:Pending,In Progress,Completed',
        ]);

        $task->update(['status' => $request->status]);

        return redirect('/')->with('success', 'Task updated!');
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return redirect('/')->with('success', 'Task deleted!');
    }
}
