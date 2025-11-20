<?php

namespace App\Http\Requests;

class UpdateTasksOrderRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ordered_ids' => ['required', 'array'],
            'ordered_ids.*' => ['integer', 'exists:tasks,id'],
            'date' => ['required', 'date_format:Y-m-d'],
        ];
    }
}
