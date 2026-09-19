<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProcessConditionGroup extends Model
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'process_id',
        'action',
        'target_step_id',
        'stop_message',
        'display_order',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (ProcessConditionGroup $group) {
            $group->id ??= (string) Str::uuid();
        });
    }

    public function process()
    {
        return $this->belongsTo(Process::class);
    }

    public function targetStep()
    {
        return $this->belongsTo(ProcessStep::class, 'target_step_id');
    }

    public function rules()
    {
        return $this->hasMany(ProcessConditionRule::class, 'group_id');
    }
}
