<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Incident::class => \App\Policies\IncidentPolicy::class,
        \App\Models\User::class => \App\Policies\UserPolicy::class,
        // Add other policies here as you create them
        // \App\Models\Shift::class => \App\Policies\ShiftPolicy::class,
        // \App\Models\SecurityDevice::class => \App\Policies\SecurityDevicePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // You can also define simple Gates here if needed
    }
}

