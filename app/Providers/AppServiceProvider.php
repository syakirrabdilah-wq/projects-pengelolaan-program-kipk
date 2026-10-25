<?php

namespace App\Providers;

use App\Models\AturanPrestasi;
use App\Models\Mahasiswa;
use App\Models\Pengaturan;
use App\Models\SemesterAkademik;
use App\Observers\AuditPerubahanObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        Paginator::useBootstrapFive();

        AturanPrestasi::observe(AuditPerubahanObserver::class);
        Mahasiswa::observe(AuditPerubahanObserver::class);
        Pengaturan::observe(AuditPerubahanObserver::class);
        SemesterAkademik::observe(AuditPerubahanObserver::class);

        View::composer('layouts.app', function ($view) {
            $pengguna = auth()->user();
            $view->with('jumlahNotifikasiBelumDibaca', $pengguna?->notifikasiBelumDibaca()->count() ?? 0);
        });
    }
}
