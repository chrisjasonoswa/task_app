<?php

namespace App\Repositories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
interface TaskRepositoryInterface
{
    public function all(User $user, array $request): Collection;
    public function create(User $user,  string $description): Task;
    public function update(User $user, int $taskId, array $data): Task;
    public function delete(User $user, int $taskId): Task;
    public function allTaskDates(User $user): SupportCollection;
    public function updateOrder(User $user, array $data): Collection;
}
