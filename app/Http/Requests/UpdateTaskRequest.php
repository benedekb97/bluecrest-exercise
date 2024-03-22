<?php

namespace App\Http\Requests;

use App\Models\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends StoreTaskRequest
{
    public function rules(): array
    {
        return [
            'title' => 'sometimes|required',
            'description' => '',
            'due_date' => 'date|after:today',
            'status' => [Rule::enum(TaskStatus::class)],
        ];
    }
}
