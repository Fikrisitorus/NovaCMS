<?php

namespace App\Providers;

use App\Models\Page;
use App\Models\Post;
use App\Models\Revision;
use App\Models\Website;
use App\Observers\PageObserver;
use App\Observers\PostObserver;
use App\Observers\WebsiteObserver;
use App\Policies\RevisionPolicy;
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

        // Sama untuk halaman: PageController meng-cache response 15 menit.
        Page::observe(PageObserver::class);

        // Sama untuk website: WebsiteController meng-cache response 15 menit.
        Website::observe(WebsiteObserver::class);

        // Policy eksplisit untuk model Revision: dipakai FilamentShield
        // untuk gate view_any revision & aksi restore di RevisionResource.
        Gate::policy(Revision::class, RevisionPolicy::class);
    }
}
