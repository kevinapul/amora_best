<?php

namespace App\Policies;

use App\Models\EventTraining;
use App\Models\User;

class EventTrainingPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            'it',
            'marketing',
            'operational',
            'finance',
            'admin',
        ]);
    }

    public function view(User $user, EventTraining $event): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['marketing', 'it']);
    }

    public function update(User $user, EventTraining $event): bool
    {
        return
            in_array($user->role, ['marketing', 'it']) &&
            !in_array($event->status, ['on_progress', 'done']);
    }

    public function delete(User $user, EventTraining $event): bool
    {
        return
            in_array($user->role, ['marketing', 'it']) &&
            $event->status !== 'done';
    }

    public function approve(User $user, EventTraining $event): bool
    {
        return
            in_array($user->role, ['marketing', 'it']) &&
            $event->status === 'pending';
    }

    public function addParticipant(User $user, EventTraining $event): bool
{
    // Kalau event DONE → hanya role tertentu
    if ($event->status === 'done') {
        return in_array($user->role, ['it']);
    }

    // Selain DONE
    return
        in_array($user->role, ['marketing', 'operational', 'it']) &&
        in_array($event->status, ['active', 'on_progress']);
}

}
