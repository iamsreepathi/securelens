<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeadLetterJob extends Model
{
    /** @use HasFactory<\Database\Factories\DeadLetterJobFactory> */
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'connection',
        'queue',
        'job_uuid',
        'job_name',
        'payload',
        'exception',
        'failed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'failed_at' => 'immutable_datetime',
        ];
    }
}
