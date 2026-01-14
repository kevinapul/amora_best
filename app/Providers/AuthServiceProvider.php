<?php

namespace App\Providers;

use App\Models\EventTraining;
use App\Policies\EventTrainingPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
    EventTraining::class => EventTrainingPolicy::class,
    EventTrainingGroup::class  => EventTrainingGroupPolicy::class,
];

    public function boot(): void
    {
        $this->registerPolicies();

    }
}
