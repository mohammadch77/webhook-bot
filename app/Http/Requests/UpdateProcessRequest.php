<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProcessRequest extends FormRequest
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
            'fields' => ['array'],
            'fields.*.label' => ['required', 'string', 'max:150'],
            'fields.*.field_type' => ['required', 'in:text,number,phone,textarea,select,boolean,file,image,date'],
            'fields.*.is_required' => ['boolean'],
            'fields.*.options' => ['nullable', 'array'],
            'fields.*.options.*.value' => ['required_with:fields.*.options', 'string', 'max:100'],
            'fields.*.options.*.label' => ['required_with:fields.*.options', 'string', 'max:100'],
        ];
    }
}
