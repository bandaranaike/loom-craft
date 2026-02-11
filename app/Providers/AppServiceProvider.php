<?php

namespace App\Providers;

use App\Contracts\VideoUploader;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Policies\AdminPolicy;
use App\Policies\CartPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use App\Policies\VendorPolicy;
use App\Services\Video\YouTubeVideoUploader;
use Carbon\CarbonImmutable;
use Google\Client;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Client::class, function (): Client {
            $client = new Client;
            $client->setClientId(config('services.youtube.client_id'));
            $client->setClientSecret(config('services.youtube.client_secret'));

            return $client;
        });

        $this->app->bind(VideoUploader::class, function (): YouTubeVideoUploader {
            $client = app(Client::class);
            $refreshToken = config('services.youtube.refresh_token');

            if (is_string($refreshToken) && $refreshToken !== '') {
                $client->setAccessToken([
                    'refresh_token' => $refreshToken,
                ]);
            }

            return new YouTubeVideoUploader($client);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->registerPolicies();
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }

    protected function registerPolicies(): void
    {
        Gate::policy(User::class, AdminPolicy::class);
        Gate::policy(Cart::class, CartPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Vendor::class, VendorPolicy::class);
    }
}
