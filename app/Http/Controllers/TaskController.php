<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    public function index()
    {
        try {
            $tasks = Task::where('user_id', Auth::id())->get();
            return response()->json($tasks, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching tasks', [
                'user_id' => Auth::id(),
                'exception' => $e,
            ]);
            return response()->json(['error' => 'Unable to fetch tasks.'], 500);
        }
    }

    public function show(Task $task)
    {
        try {
            Gate::authorize('view', $task);
            return response()->json($task, 200);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized task view attempt', [
                'user_id' => Auth::id(),
                'task_id' => $task->id,
            ]);
            return response()->json(['error' => 'Forbidden'], 403);
        } catch (\Exception $e) {
            Log::error('Error fetching task', [
                'user_id' => Auth::id(),
                'task_id' => $task->id,
                'exception' => $e,
            ]);
            return response()->json(['error' => 'Unable to fetch task.'], 500);
        }
    }

    public function store(StoreTaskRequest $request)
    {
        try {
            $validated = $request->validated();

            $validated['time'] = Carbon::parse($validated['time']);

            $task = Task::create([
                ...$validated,
                'user_id' => Auth::id(),
            ]);

            if ($request->hasFile('attachment')) {
                $path = $request->file('attachment')->store('attachments', 'public');
                $task->update(['attachment' => $path]);
            }

            return response()->json($task, 201);
        } catch (\Exception $e) {
            Log::error('Error creating task', [
                'user_id' => Auth::id(),
                'exception' => $e,
            ]);
            return response()->json(['error' => 'Unable to create task.'], 500);
        }
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        try {
            Gate::authorize('update', $task);

            $task->update($request->validated());

            if ($request->hasFile('attachment')) {
                if ($task->attachment) {
                    Storage::disk('public')->delete($task->attachment);
                }
                $path = $request->file('attachment')->store('attachments', 'public');
                $task->update(['attachment' => $path]);
            }

            return response()->json($task, 200);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized task update attempt', [
                'user_id' => Auth::id(),
                'task_id' => $task->id,
            ]);

            return response()->json(['error' => 'Forbidden'], 403);
        } catch (\Exception $e) {
            Log::error('Error updating task', [
                'user_id' => Auth::id(),
                'task_id' => $task->id,
                'exception' => $e,
            ]);

            return response()->json(['error' => 'Unable to update task.'], 500);
        }
    }

    public function destroy(Task $task)
    {
        try {
            Gate::authorize('delete', $task);

            if ($task->attachment) {
                Storage::disk('public')->delete($task->attachment);
            }

            $task->delete();

            return response()->json(['message' => 'Task deleted.'], 200);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized task delete attempt', [
                'user_id' => Auth::id(),
                'task_id' => $task->id,
            ]);

            return response()->json(['error' => 'Forbidden'], 403);
        } catch (\Exception $e) {
            Log::error('Error deleting task', [
                'user_id' => Auth::id(),
                'task_id' => $task->id,
                'exception' => $e,
            ]);

            return response()->json(['error' => 'Unable to delete task.'], 500);
        }
    }
}
