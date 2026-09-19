<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProcessField extends Model
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'step_id',
        'field_key',
        'label',
        'field_type',
        'is_required',
        'options',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'options' => 'array',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (ProcessField $field) {
            $field->id ??= (string) Str::uuid();
            $field->field_key ??= Str::slug($field->label);
        });
    }

    public function step()
    {
        return $this->belongsTo(ProcessStep::class, 'step_id');
    }

    public function conditionRules()
    {
        return $this->hasMany(ProcessConditionRule::class, 'field_id');
    }
}
