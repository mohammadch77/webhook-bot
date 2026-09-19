<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ConversationSession extends Model
{
    public $timestamps = false;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'bot_id',
        'external_user_id',
        'process_id',
        'submission_id',
        'current_step_id',
        'current_field_id',
        'state',
        'last_activity_at',
    ];

    protected function casts(): array
    {
        return [
            'state' => 'array',
            'last_activity_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (ConversationSession $session) {
            $session->id ??= (string) Str::uuid();
        });
    }

    public function bot()
    {
        return $this->belongsTo(Bot::class);
    }

    public function process()
    {
        return $this->belongsTo(Process::class);
    }

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    public function currentStep()
    {
        return $this->belongsTo(ProcessStep::class, 'current_step_id');
    }

    public function currentField()
    {
        return $this->belongsTo(ProcessField::class, 'current_field_id');
    }
}
