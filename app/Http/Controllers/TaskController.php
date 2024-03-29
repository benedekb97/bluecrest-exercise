<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;
use function Symfony\Component\String\b;

class TaskController extends Controller
{
    public function index(Request $request): JsonResource
    {
        return TaskResource::collection(Task::paginate());
    }

    public function store(StoreTaskRequest $request): Response
    {
        $validatedRequest = $request->validated();

        $task = new Task();

        $task->title = $validatedRequest['title'];
        $task->due_date = $validatedRequest['due_date'] ?? null;
        $task->description = $validatedRequest['description'] ?? null;
        $task->status = $validatedRequest['status'] ?? TaskStatus::DRAFT;

        $task->save();

        return new JsonResponse(
            new TaskResource($task),
            Response::HTTP_CREATED
        );
    }

    public function show(string $id): JsonResource|JsonResponse
    {
        $task = Task::all()->find($id);

        if (null === $task) {
            return $this->generateNotFoundResponse();
        }

        return new TaskResource(Task::all()->find($id));
    }

    public function update(string $id, UpdateTaskRequest $request): JsonResource|Response
    {
        $task = Task::all()->find($id);

        if (null === $task) {
            return $this->generateNotFoundResponse();
        }

        $validatedRequest = $request->validated();

        $task->title = $validatedRequest['title'] ?? $task->title;
        $task->description = $request->has('description') ? $validatedRequest['description'] : $task->description;
        $task->due_date = $validatedRequest['due_date'] ?? $task->due_date;
        $task->status = $validatedRequest['status'] ?? $task->status;

        $task->save();

        return new TaskResource($task);
    }

    public function delete(string $id): Response
    {
        $task = Task::all()->find($id);

        if (null === $task) {
            return $this->generateNotFoundResponse();
        }

        $task->delete();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function generateNotFoundResponse(): JsonResponse
    {
        return new JsonResponse(
            [
                'message' => 'The requested resource could not be found!',
            ],
            Response::HTTP_NOT_FOUND
        );
    }
}
