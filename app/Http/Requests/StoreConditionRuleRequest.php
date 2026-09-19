<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConditionRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $processId = $this->route('process')->id;

        return [
            'field_id' => [
                'required',
                Rule::exists('process_fields', 'id')->where(
                    fn ($query) => $query->whereIn('step_id', function ($sub) use ($processId) {
                        $sub->select('id')->from('process_steps')->where('process_id', $processId);
                    })
                ),
            ],
            'operator' => ['required', 'in:=,!=,>,<,>=,<='],
            'value' => ['required', 'string', 'max:200'],
        ];
    }
}
