<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProcessConditionRule extends Model
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'group_id',
        'field_id',
        'operator',
        'value',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (ProcessConditionRule $rule) {
            $rule->id ??= (string) Str::uuid();
        });
    }

    public function group()
    {
        return $this->belongsTo(ProcessConditionGroup::class, 'group_id');
    }

    public function field()
    {
        return $this->belongsTo(ProcessField::class, 'field_id');
    }
}
