<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Bot extends Model
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'platform',
        'name',
        'token',
        'webhook_url',
        'status',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Bot $bot) {
            $bot->id ??= (string) Str::uuid();
        });
    }

    public function processPlatforms()
    {
        return $this->hasMany(ProcessPlatform::class);
    }

    public function processes()
    {
        return $this->belongsToMany(Process::class, 'process_platforms');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function conversationSessions()
    {
        return $this->hasMany(ConversationSession::class);
    }
}
