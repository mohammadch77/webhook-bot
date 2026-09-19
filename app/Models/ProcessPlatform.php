<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProcessPlatform extends Model
{
    public $timestamps = false;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'process_id',
        'bot_id',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (ProcessPlatform $processPlatform) {
            $processPlatform->id ??= (string) Str::uuid();
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
}
