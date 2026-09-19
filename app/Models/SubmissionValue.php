<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SubmissionValue extends Model
{
    const UPDATED_AT = null;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'submission_id',
        'field_id',
        'value',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (SubmissionValue $value) {
            $value->id ??= (string) Str::uuid();
        });
    }

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    public function field()
    {
        return $this->belongsTo(ProcessField::class, 'field_id');
    }
}
