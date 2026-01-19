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

public function viewLaporan(User $user)
{
    return $user->hasRole(['finance', 'it']);
}


public function approveFinance(User $user, EventTraining $event): bool
{
    return $this->updateFinance($user, $event);
}

public function updateFinance(User $user, EventTraining $event): bool
{
    return $user->hasRole(['finance', 'it'])
        && $event->status === 'done'
        && ! $event->finance_approved;
}



    public function view(User $user, EventTraining $eventTraining): bool
    {
        return $this->viewAny($user);
    }

    public function viewPending(User $user): bool
{
    return in_array($user->role, [
        'it',
        'marketing',
        'boss',
    ]);
}

    public function create(User $user): bool
    {
        return in_array($user->role, ['marketing', 'it']);
    }

    public function update(User $user, EventTraining $eventTraining): bool
    {
        return
            in_array($user->role, ['marketing', 'it']) &&
            !in_array($eventTraining->status, ['on_progress', 'done']);
    }

    public function delete(User $user, EventTraining $eventTraining): bool
    {
        return
            in_array($user->role, ['marketing', 'it']) &&
            $eventTraining->status !== 'done';
    }


   public function approve(User $user, ?EventTraining $eventTraining = null): bool
{
    // cek role dulu
    if ($user->role !== 'it') {
        return false;
    }

    // kalau dipanggil TANPA model (approve global)
    if ($eventTraining === null) {
        return true;
    }

    // kalau dipanggil DENGAN model
    return $eventTraining->status === 'pending';
}


public function addParticipant(User $user, EventTraining $event): bool
{
    if ($event->status === 'done') {
        return $user->hasRole(['it']); // Hanya IT bisa tambah peserta setelah DONE
    }
    return $user->hasRole(['it', 'marketing', 'operational'])
        && in_array($event->status, ['active', 'on_progress']);
}
}
