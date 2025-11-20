<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetAllTasksRequest;
use App\Http\Requests\UpdateTasksOrderRequest;
use Illuminate\Http\Request; 
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Repositories\TaskRepositoryInterface;
use App\Http\Resources\ApiResponseResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\AuthorizationException;
use Log;

class TaskController extends Controller
{
    protected TaskRepositoryInterface $taskRepo;

    public function __construct(TaskRepositoryInterface $taskRepo)
    {
        $this->taskRepo = $taskRepo;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(GetAllTasksRequest $request)
    {
        try{
            $user = $request->user();
            $validated = $request->validated();
            $tasks = $this->taskRepo->all($user, $validated);

            return ApiResponseResource::make([
                'success' => true,
                'message' => 'Tasks are fetched successfully.',
                'data'    => TaskResource::collection($tasks),
            ])->response()->setStatusCode(200);
        }catch(\Exception $e){
            Log::error('An error occured while fetching the tasks:' . $e->getMessage());
            return ApiResponseResource::make([
                'message' => 'An unexpected error occured',
            ])->response()->setStatusCode(code: 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        try{
            Gate::authorize('create', Task::class);
            $user = $request->user();
            $validated = $request->validated();
            $task = $this->taskRepo->create($user, $validated['description']);
            return ApiResponseResource::make([
                'success' => true,
                'message' => 'New task is successfully added',
                'data'    => TaskResource::make($task),
            ])->response()->setStatusCode(200);
        }catch(\Exception $e){
            Log::error('An error occured while adding new task:' . $e->getMessage());
            return ApiResponseResource::make([
                'message' => 'An unexpected error occured',
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        try{
            Gate::authorize('update', $task);
            $user = $request->user();
            $validated = $request->validated();
            $task = $this->taskRepo->update($user, $task->id, $validated);
            return ApiResponseResource::make([
                'success' => true,
                'message' => 'Task is successfully updated.',
                'data'    => TaskResource::make($task),
            ])->response()->setStatusCode(200);
        }catch (AuthorizationException $e) {
            return ApiResponseResource::make([
                'message' => $e->getMessage(), 
            ])->response()->setStatusCode(404);
        }
        catch(\Exception $e){
            Log::error('An error occured while updating the task:' . $e->getMessage());
            return ApiResponseResource::make([
                'message' => 'An unexpected error occured',
            ])->response()->setStatusCode(500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Task $task)
    {
        try{
            Gate::authorize('delete', $task);
            $user = $request->user();
            $task = $this->taskRepo->delete($user, $task->id);

            return ApiResponseResource::make([
                'success' => true,
                'message' => 'Task is successfully delered.',
                'data'    => TaskResource::make($task),
            ])->response()->setStatusCode(200);
        }
        catch (AuthorizationException $e) {
            return ApiResponseResource::make([
                'message' => $e->getMessage(), 
            ])->response()->setStatusCode(404);
        }
        catch(\Exception $e){
            Log::error('An error occured while deleting the task:' . $e->getMessage());
            return ApiResponseResource::make([
                'message' => 'An unexpected error occured',
            ])->response()->setStatusCode(500);
        }
    }

    /**
     * Retrieve all task dates
     */
    public function dates(Request $request){
        try{
            $user = $request->user();
            $taskDates = $this->taskRepo->allTaskDates($user);
            return ApiResponseResource::make([
                'success' => true,
                'message' => 'Tasks dates are fetched successfully.',
                'data'    => $taskDates,
            ])->response()->setStatusCode(200);
        }catch(\Exception $e){
            Log::error('An error occured while fetching the task dates:' . $e->getMessage());
            return ApiResponseResource::make([
                'message' => 'An unexpected error occured',
            ])->response()->setStatusCode(code: 500);
        }
    }

    /**
     * Update order of the tasks
     */
    public function updateOrder(UpdateTasksOrderRequest $request)
    {
        try{
            Gate::authorize('updateOrder', [Task::class, $request->date]);
            $user = $request->user();
            $validated = $request->validated();
            $task = $this->taskRepo->updateOrder($user, $validated);
            return ApiResponseResource::make([
                'success' => true,
                'message' => 'Tasks are successfully updated.',
                'data'    => TaskResource::collection($task),
            ])->response()->setStatusCode(200);
        }catch(AuthorizationException $e){
            return ApiResponseResource::make([
                'message' => $e->getMessage(),
            ])->response()->setStatusCode(404);
        }
        catch(\Exception $e){
            Log::error('An error occured while reordering the task:' . $e->getMessage());
            return ApiResponseResource::make([
                'message' => 'An unexpected error occured',
            ])->response()->setStatusCode(500);
        }
    }
}
