<?php

namespace Mortezamasumi\FbActivity\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\Activitylog\Models\Activity;

class FbActivityPolicy
{
    use HandlesAuthorization;

    public function viewAny($user): bool
    {
        return $user->can('view_any_fb::activity');
    }

    public function view($user, Activity $activity): bool
    {
        return $user->can('view_fb::activity');
    }

    public function delete($user, Activity $activity): bool
    {
        return $user->can('delete_fb::activity');
    }

    public function export($user): bool
    {
        return $user->can('export_fb::activity');
    }

    public function viewAllUsers($user): bool
    {
        return $user->can('view_all_users_fb::activity');
    }
}
