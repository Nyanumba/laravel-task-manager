<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tasks')->where(function ($query) {
                    return $query->where('due_date', $this->input('due_date'));
                }),
            ],
            'due_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
            ],
            'priority' => [
                'required',
                Rule::in(Task::allPriorities()),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.unique'            => 'A task with this title already exists for that due date.',
            'due_date.after_or_equal' => 'The due date must be today or a future date.',
            'priority.in'             => 'Priority must be: low, medium, or high.',
        ];
    }
}