<?php

namespace App\Policies;

use App\Models\EventTrainingGroup;
use App\Models\User;

class EventTrainingGroupPolicy
{
    public function approve(User $user, EventTrainingGroup $group): bool
    {
        return in_array($user->role, ['marketing', 'it'])
            && $group->events->contains(fn ($e) => $e->status === 'pending');
    }

    public function view(User $user): bool
    {
        return in_array($user->role, [
            'marketing',
            'it',
            'operational',
            'finance',
            'admin'
        ]);
    }
public function viewFinance(User $user): bool
{
    return in_array($user->role, ['finance', 'it']);
}



public function approveFinance(User $user, EventTrainingGroup $group): bool
{
    return in_array($user->role, ['finance', 'it'])
        && ! $group->finance_approved;
}
}
