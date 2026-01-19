<?php

namespace App\Providers;

use App\Models\EventTraining;
use App\Models\EventTrainingGroup;
use App\Policies\EventTrainingPolicy;
use App\Policies\EventTrainingGroupPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        EventTraining::class      => EventTrainingPolicy::class,
        EventTrainingGroup::class => EventTrainingGroupPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
