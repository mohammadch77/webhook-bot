<?php

namespace App\Bot\Engines;

use App\Bot\DTOs\ValidationResult;
use App\Models\ProcessField;

class ValidationEngine
{
    public function validate(ProcessField $field, string $value): ValidationResult
    {
        if ($field->is_required && trim($value) === '') {
            return ValidationResult::fail('این فیلد اجباری است.');
        }

        if (! $field->is_required && trim($value) === '') {
            return ValidationResult::ok();
        }

        return match ($field->field_type) {
            'text', 'textarea' => ValidationResult::ok(),
            'number' => $this->validateNumber($value),
            'phone' => $this->validatePhone($value),
            'select' => $this->validateSelect($field, $value),
            'boolean' => $this->validateBoolean($value),
            'date' => $this->validateDate($value),
            'file', 'image' => ValidationResult::ok(),
            default => ValidationResult::ok(),
        };
    }

    protected function validateNumber(string $value): ValidationResult
    {
        return is_numeric($value)
            ? ValidationResult::ok()
            : ValidationResult::fail('لطفاً یک عدد معتبر وارد کنید.');
    }

    protected function validatePhone(string $value): ValidationResult
    {
        return preg_match('/^09[0-9]{9}$/', $value)
            ? ValidationResult::ok()
            : ValidationResult::fail('شماره موبایل معتبر نیست. فرمت صحیح: 09xxxxxxxxx');
    }

    protected function validateSelect(ProcessField $field, string $value): ValidationResult
    {
        $options = collect($field->options ?? [])->pluck('value')->all();

        return in_array($value, $options, true)
            ? ValidationResult::ok()
            : ValidationResult::fail('گزینه انتخاب‌شده معتبر نیست.');
    }

    protected function validateBoolean(string $value): ValidationResult
    {
        return in_array($value, ['true', 'false'], true)
            ? ValidationResult::ok()
            : ValidationResult::fail('لطفاً یکی از گزینه‌های بله یا خیر را انتخاب کنید.');
    }

    protected function validateDate(string $value): ValidationResult
    {
        return preg_match('/^1[0-4][0-9]{2}\/(0[1-9]|1[0-2])\/(0[1-9]|[12][0-9]|3[01])$/', $value)
            ? ValidationResult::ok()
            : ValidationResult::fail('فرمت تاریخ معتبر نیست. فرمت صحیح: 1403/06/15');
    }
}
