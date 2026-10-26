<?php

namespace App\Providers;

use App\Http\Responses\LoginResponse;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use TallStackUi\Facades\TallStackUi;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        TallStackUi::customize()
            ->toast()
            ->block([
                'wrapper.first' => 'pointer-events-none fixed inset-0 flex flex-col items-end justify-end gap-y-3 px-5 py-5',
                'wrapper.second' => 'flex w-full flex-col items-center space-y-3',
                'wrapper.third' => 'pointer-events-auto w-full max-w-sm overflow-hidden rounded-sm border border-ink/20 bg-paper shadow-md',
                'wrapper.fourth' => 'flex gap-3 p-4',
                'icon.size' => 'h-5 w-5',
                'content.wrapper' => 'w-0 flex-1',
                'content.text' => 'text-sm font-semibold text-ink',
                'content.text-confirm' => 'font-semibold text-ink',
                'content.description' => 'mt-1 text-sm leading-5 text-ink/60',
                'buttons.wrapper.second' => 'flex min-h-full flex-col justify-between',
                'buttons.close.wrapper' => 'flex shrink-0',
                'buttons.close.class' => 'inline-flex text-ink/40 transition hover:text-ink focus:outline-hidden focus:ring-0 cursor-pointer',
                'buttons.close.size' => 'h-4 w-4',
                'progress.wrapper' => 'relative h-px w-full bg-ink/10',
                'progress.bar' => 'absolute h-full w-24 bg-clay duration-300 ease-linear',
            ]);
    }
}
