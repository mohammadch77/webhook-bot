<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Submission extends Model
{
    public $timestamps = false;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'process_id',
        'bot_id',
        'platform',
        'external_user_id',
        'username',
        'status',
        'started_at',
        'completed_at',
        'admin_action',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Submission $submission) {
            $submission->id ??= (string) Str::uuid();
        });
    }

    public function process()
    {
        return $this->belongsTo(Process::class);
    }

    public function bot()
    {
        return $this->belongsTo(Bot::class);
    }

    public function values()
    {
        return $this->hasMany(SubmissionValue::class);
    }

    public function conversationSession()
    {
        return $this->hasOne(ConversationSession::class);
    }
}
