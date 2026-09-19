<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Process extends Model
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'process_key',
        'version',
        'is_current_version',
        'description',
        'is_active',
        'created_by_admin_id',
    ];

    protected function casts(): array
    {
        return [
            'is_current_version' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Process $process) {
            $process->id ??= (string) Str::uuid();

            if (! $process->process_key) {
                $process->process_key = static::generateUniqueProcessKey($process->name);
            }
        });
    }

    public static function generateUniqueProcessKey(string $name): string
    {
        $base = Str::slug($name);
        $key = $base;
        $suffix = 1;

        while (static::withTrashed()->where('process_key', $key)->exists()) {
            $key = "{$base}-{$suffix}";
            $suffix++;
        }

        return $key;
    }

    public function createdByAdmin()
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    public function processPlatforms()
    {
        return $this->hasMany(ProcessPlatform::class);
    }

    public function bots()
    {
        return $this->belongsToMany(Bot::class, 'process_platforms');
    }

    public function syncBots(array $botIds): void
    {
        $existing = $this->processPlatforms()->pluck('bot_id')->all();

        $toRemove = array_diff($existing, $botIds);
        $toAdd = array_diff($botIds, $existing);

        if ($toRemove) {
            $this->processPlatforms()->whereIn('bot_id', $toRemove)->delete();
        }

        foreach ($toAdd as $botId) {
            $this->processPlatforms()->create(['bot_id' => $botId]);
        }
    }

    public function steps()
    {
        return $this->hasMany(ProcessStep::class);
    }

    public function conditionGroups()
    {
        return $this->hasMany(ProcessConditionGroup::class);
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
