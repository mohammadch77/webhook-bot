<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConditionGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $processId = $this->route('process')->id;

        return [
            'action' => ['required', 'in:show_step,skip_step,jump_to_step,stop'],
            'target_step_id' => [
                Rule::requiredIf($this->action !== 'stop'),
                'nullable',
                Rule::exists('process_steps', 'id')->where('process_id', $processId),
            ],
            'stop_message' => [
                Rule::requiredIf($this->action === 'stop'),
                'nullable', 'string', 'max:300',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->action === 'stop') {
            $this->merge(['target_step_id' => null]);
        } else {
            $this->merge(['stop_message' => null]);
        }
    }
}
