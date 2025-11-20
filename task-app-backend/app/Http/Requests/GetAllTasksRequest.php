<?php

namespace App\Http\Requests;


class GetAllTasksRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date' => 'nullable|date_format:Y-m-d',
            'search' => 'nullable|string',
        ];
    }
}
