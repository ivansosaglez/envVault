<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ActivityAction;
use App\Models\Activity;
use App\Models\Project;

/**
 * Appends an entry to a project's activity log.
 *
 * Only the action and the *name* of what was affected are stored. Values never
 * reach the log, secret or not.
 */
class RecordActivity
{
    public function __invoke(Project $project, ActivityAction $action, ?string $subject = null): Activity
    {
        return $project->activities()->create([
            'user_id' => auth()->id() ?? $project->user_id,
            'action' => $action,
            'subject' => $subject,
        ]);
    }
}
