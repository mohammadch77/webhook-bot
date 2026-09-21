<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProcessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:300'],
            'is_active' => ['boolean'],
            'bot_ids' => ['array'],
            'bot_ids.*' => ['uuid', 'exists:bots,id'],
            'steps' => ['array'],
            'steps.*.name' => ['required', 'string', 'max:100'],
            'steps.*.fields' => ['array'],
            'steps.*.fields.*.label' => ['required', 'string', 'max:150'],
            'steps.*.fields.*.field_type' => ['required', 'in:text,number,phone,textarea,select,boolean,file,image,date'],
            'steps.*.fields.*.is_required' => ['boolean'],
            'steps.*.fields.*.options' => ['nullable', 'array'],
            'steps.*.fields.*.options.*.value' => ['required_with:steps.*.fields.*.options', 'string', 'max:100'],
            'steps.*.fields.*.options.*.label' => ['required_with:steps.*.fields.*.options', 'string', 'max:100'],
        ];
    }
}
