<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'platform' => ['required', 'in:telegram,bale,rubika'],
            'token' => [
                'required',
                'string',
                'max:255',
                Rule::unique('bots', 'token')->where(fn ($query) => $query->where('platform', $this->platform)),
            ],
        ];
    }
}
