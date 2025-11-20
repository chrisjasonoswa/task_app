<?php

namespace App\Repositories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Carbon;
use Log;
class TaskRepository implements TaskRepositoryInterface
{
    public function all(User $user, array $request): Collection
    {
        $query = $user->tasks()->orderBy('created_at', 'desc')
            ->orderBy('priority');
        // Filter by search term in description
        if (!empty($request['search'])) {
            $query->where('description', 'like', '%' . $request['search'] . '%');
        }

        // Filter by date (created_at)
        if (!empty($request['date'])) {
            $date = Carbon::parse($request['date'])->format('Y-m-d');
            $query->whereDate('created_at', $date);
        }

        return $query->get();
    }

    public function create(User $user, string $description): Task
    {
        // Get today's date string
        $today = now()->format('Y-m-d');

        // Find the current max priority for tasks created today
        $maxPriority = $user->tasks()
            ->whereDate('created_at', $today)
            ->max('priority');

        // Set the next priority
        $nextPriority = $maxPriority ? $maxPriority + 1 : 1;

        return $user->tasks()->create([
            'description' => $description,
            'priority'    => $nextPriority,
        ]);
    }

    public function update(User $user, int $taskId, array $data): Task
    {
        $task = $user->tasks()->findOrFail($taskId);
        // Only update fields if present
        $task->update(array_intersect_key(
            $data, 
            array_flip(['is_completed','description'])
        ));
        return $task;
    }

    public function delete(User $user, int $taskId): Task
    {
        $task = $user->tasks()->findOrFail($taskId);
        $task->delete();
        return $task;
    }

    /**
     * Get all unique task dates
     */
    public function allTaskDates(User $user): SupportCollection
    {
        return $user->tasks()
            ->orderBy('created_at', 'desc')
            ->pluck('created_at')
            ->map(fn($date) => $date->format('Y-m-d'))
            ->unique() 
            ->values();
    }

    /**
     * Update tasks' order
     */
    public function updateOrder(User $user, array $data): Collection
    {
        $orderedIds = $data['ordered_ids'];
        $date = $data['date'];
    
        // Fetch the tasks of user on the given date
        $tasks = $user->tasks()
            ->whereDate('created_at', $date)
            ->orderBy('priority')
            ->get()
            ->keyBy('id');

        $priority = 1;

        // First, update the tasks that are included in the orderedIds array
        foreach ($orderedIds as $id) {
            $task = $tasks->get($id);
            if ($task) {
                $task->priority = $priority++;
                $task->save();
            }
        }

        // Then, assign priority to the tasks that were not included, keeping their relative order
        foreach ($tasks as $task) {
            if (!in_array($task->id, $orderedIds)) {
                $task->priority = $priority++;
                $task->save();
            }
        }

        // Return updated tasks as a collection
        return $tasks->sortBy('priority')->values();
    }
}
