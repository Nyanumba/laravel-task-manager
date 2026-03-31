<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in(Task::allStatuses()),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Status must be: pending, in_progress, or done.',
        ];
    }
}