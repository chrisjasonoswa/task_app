<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Log;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): Response
    {
        return $user->id === $task->user_id
            ? Response::allow()
            : Response::deny('Failed to save data. Task cannot be found.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): Response
    {
        return $user->id === $task->user_id
            ? Response::allow()
            : Response::deny('Failed to delete data. Task cannot be found.');
    }

    /**
     * Determine whether the user can reorder the model.
     */
    public function updateOrder(User $user, string $date): Response
    {
        Log::info('updateOrder');
        return $user->tasks()->whereDate('created_at', $date)->exists()
            ? Response::allow()
            : Response::deny('Failed to save data. No tasks belong on the given date');
    }
}
