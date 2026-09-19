<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProcessStep extends Model
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'process_id',
        'step_key',
        'name',
        'display_order',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (ProcessStep $step) {
            $step->id ??= (string) Str::uuid();
            $step->step_key ??= Str::slug($step->name);
        });
    }

    public function process()
    {
        return $this->belongsTo(Process::class);
    }

    public function fields()
    {
        return $this->hasMany(ProcessField::class, 'step_id');
    }

    public function targetedByConditionGroups()
    {
        return $this->hasMany(ProcessConditionGroup::class, 'target_step_id');
    }
}
