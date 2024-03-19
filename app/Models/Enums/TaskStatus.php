<?php

declare(strict_types=1);

namespace App\Models\Enums;

enum TaskStatus: string
{
    case DRAFT = 'draft';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
}
