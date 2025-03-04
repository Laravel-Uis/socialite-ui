<?php

namespace SocialiteUi\Policies;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SocialAccountPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SocialAccount $socialAccount): bool
    {
        return $user->ownsSocialAccount($socialAccount);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SocialAccount $socialAccount): bool
    {
        return $user->ownsSocialAccount($socialAccount);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SocialAccount $socialAccount): bool
    {
        return $user->ownsSocialAccount($socialAccount);
    }
}
