<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>To-Do List</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md p-6 bg-white rounded-xl shadow-lg">
        <h1 class="text-2xl font-bold mb-4 text-center text-gray-800">To-Do List</h1>

        <form action="/tasks" method="POST" class="flex gap-2 mb-6">
            @csrf
            <input
                type="text"
                name="title"
                placeholder="New task"
                required
                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            <button
                type="submit"
                class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition"
            >
                Add
            </button>
        </form>

        <ul class="space-y-3">
            @foreach($tasks as $task)
                <li class="flex items-center justify-between bg-gray-50 p-3 rounded-lg shadow-sm">
                    <span class="{{ $task->completed ? 'line-through text-gray-400' : 'text-gray-800' }}">
                        {{ $task->title }}
                    </span>

                    <div class="flex gap-2">
                        @if(!$task->completed)
                            <form action="/tasks/{{ $task->id }}/complete" method="POST">
                                @csrf
                                @method('PATCH')
                                <button
                                    type="submit"
                                    class="text-green-600 hover:text-green-800"
                                    title="Mark as complete"
                                >
                                    ✅
                                </button>
                            </form>
                        @else
                            <span class="text-green-600" title="Completed">✔️</span>
                        @endif

                        <form action="/tasks/{{ $task->id }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="text-red-500 hover:text-red-700"
                                title="Delete task"
                            >
                                ❌
                            </button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</body>
</html>
