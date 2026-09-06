<?php

namespace Mortezamasumi\FbActivity\Contracts;

use Spatie\Activitylog\Models\Activity;

/**
 * Implement on an Eloquent model to explicitly control how fb-activity renders
 * the model as the subject/causer of an activity entry. Evaluated after the
 * per-model `fb-activity.subject.titles` config map and before the attribute
 * cascade. Return null to fall through to the next step.
 */
interface HasActivityTitle
{
    public function activityTitle(Activity $activity): ?string;
}
