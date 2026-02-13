<?php

namespace App\Events;

use App\Models\DeadLetterJob;
use Illuminate\Foundation\Events\Dispatchable;

class IngestionFailureDetected
{
    use Dispatchable;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public DeadLetterJob $deadLetterJob,
    ) {}
}
