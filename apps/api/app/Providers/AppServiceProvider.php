<?php

namespace App\Providers;

use App\Models\Post;
use App\Observers\PostObserver;
use App\Policies\RolePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Model Role milik package Spatie, sehingga Laravel tidak bisa
        // me-resolve RolePolicy secara otomatis berdasarkan konvensi nama.
        // Daftarkan secara eksplisit agar permission Filament Shield
        // (view_any_role, create_role, dst.) benar-benar diterapkan.
        Gate::policy(Role::class, RolePolicy::class);

        // Invalidate cache endpoint publik post (PostController menyimpan
        // response selama 15 menit) setiap kali post disimpan/dihapus.
        Post::observe(PostObserver::class);
    }
}
