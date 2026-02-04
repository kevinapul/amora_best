<?php

namespace App\Policies;

use App\Models\EventTraining;
use App\Models\User;

class EventTrainingPolicy
{
    /* =====================================================
     * VIEW
     * ===================================================== */

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

    public function viewPending(User $user): bool
    {
        return in_array($user->role, [
            'it',
            'marketing',
            'boss',
        ]);
    }

    public function viewLaporan(User $user): bool
    {
        return in_array($user->role, ['finance', 'it']);
    }

    /* =====================================================
     * CREATE / UPDATE / DELETE EVENT
     * ===================================================== */

    public function create(User $user): bool
    {
        return in_array($user->role, ['marketing', 'it']);
    }

    public function update(User $user, EventTraining $event): bool
    {
        return in_array($user->role, ['marketing', 'it'])
            && ! in_array($event->status, ['on_progress', 'done']);
    }

    public function delete(User $user, EventTraining $event): bool
    {
        return in_array($user->role, ['marketing', 'it'])
            && $event->status !== 'done';
    }

    /* =====================================================
     * APPROVAL (MARKETING / IT)
     * ===================================================== */

    public function approve(User $user, ?EventTraining $event = null): bool
    {
        // hanya IT yang boleh approve
        if ($user->role !== 'it') {
            return false;
        }

        // approve global (tanpa model)
        if ($event === null) {
            return true;
        }

        // approve event spesifik
        return $event->status === 'pending';
    }

    /* =====================================================
     * PARTICIPANT
     * ===================================================== */

    public function addParticipant(User $user, EventTraining $event): bool
{
    // 🔒 INHOUSE + FINANCE APPROVED = TOTAL LOCK
    if (
        $event->isInhouse() &&
        $event->eventTrainingGroup->finance_approved
    ) {
        return false;
    }

    if ($event->status === 'done') {
        return $user->role === 'it';
    }

    return in_array($user->role, ['marketing', 'it'])
        && in_array($event->status, ['active', 'on_progress']);
}


    public function addInstructor(User $user, EventTraining $event): bool
    {
        if ($event->status === 'done') {
            return $user->role === 'it';
        }
        return in_array($user->role, ['operational', 'it'])
            && in_array($event->status, ['active', 'on_progress']);
    }

    /* =====================================================
     * FINANCE 
     * ===================================================== */

public function approveFinance(User $user, EventTraining $event): bool
{
    return $user->hasRole(['finance', 'it'])
        && $event->status === 'done'
        && ! $event->eventTrainingGroup->finance_approved;
}


public function bulkPayment(User $user, EventTraining $event): bool
{
    return $user->hasRole(['finance', 'it'])
        && $event->status === 'done'
        && ! $event->finance_approved;
}



}
