<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Post;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // Define your policies here
    ];

    public function boot(): void
    {
        $this->registerPolicies();
        // registrasi gate untuk permission
        $this->registerPermissionGates();
    }

    private function registerPermissionGates(): void
    {
        // lakukan caching untuk permission agar tidak membebani aplikasi terutama query ke database
        $permissions = Cache::remember('permissions', 3600, function () {
            return \App\Models\Permission::all();
        });
        
        // definisikan gate untuk setiap permission
        foreach ($permissions as $permission) {
            Gate::define($permission->name, function (User $user) use ($permission) {
                // check apakah user memiliki permission
                return $user->hasPermission($permission);
            });
        }
    }
}