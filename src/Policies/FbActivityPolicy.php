<?php

declare(strict_types=1);

namespace Mortezamasumi\FbActivity\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Spatie\Activitylog\Models\Activity;

class FbActivityPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Activity');
    }

    public function view(AuthUser $authUser, Activity $activity): bool
    {
        return $authUser->can('View:Activity');
    }

    public function delete(AuthUser $authUser, Activity $activity): bool
    {
        return $authUser->can('Delete:Activity');
    }

    public function export(AuthUser $authUser): bool
    {
        return $authUser->can('Export:Activity');
    }

    public function viewAllUsers(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAllUsers:Activity');
    }
}
