<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreProcessFieldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:150'],
            'field_type' => ['required', 'in:text,number,phone,textarea,select,boolean,file,image,date'],
            'is_required' => ['boolean'],
            'options' => ['nullable', 'array'],
            'options.*.value' => ['required_with:options', 'string', 'max:100'],
            'options.*.label' => ['required_with:options', 'string', 'max:100'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->field_type === 'boolean' && $this->boolean('is_required')) {
                $validator->errors()->add('is_required', 'فیلد boolean نمی‌تواند اجباری باشد.');
            }

            if ($this->field_type === 'select' && empty($this->input('options'))) {
                $validator->errors()->add('options', 'برای فیلد select حداقل یک گزینه لازم است.');
            }
        });
    }
}
