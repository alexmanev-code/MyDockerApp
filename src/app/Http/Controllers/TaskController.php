<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index() {
        $tasks = Task::all();
        return view('tasks.index', compact('tasks'));
    }

    public function store(Request $request) {
        $request->validate(['title' => 'required']);
        Task::create(['title' => $request->title]);
        return redirect('/');
    }

    public function destroy(Task $task) {
        $task->delete();
        return redirect('/');
    }

    public function complete(Task $task) {
        $task->update(['completed' => true]);
        return redirect('/');
    }
}

