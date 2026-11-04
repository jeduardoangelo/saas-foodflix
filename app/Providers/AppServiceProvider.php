<?php

namespace App\Providers;

use App\Services\Cart\CartService;
use App\Services\Payment\FakePaymentGateway;
use App\Services\Payment\LoggingPaymentGateway;
use App\Services\Payment\PaymentGatewayInterface;
use InvalidArgumentException;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Resources\Json\JsonResource;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(PaymentGatewayInterface::class, function () {
            $driver = config('services.payment.driver');

            if ($driver === 'fake') {
                return new LoggingPaymentGateway(
                    new FakePaymentGateway(
                        (bool) config('services.payment.fake.approved', true)
                    )
                );
            }

            throw new InvalidArgumentException("Gateway de pagamento não suportado: {$driver}.");
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        JsonResource::withoutWrapping();

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        View::composer('site.layouts.app', function ($view) {
            $view->with('cartSummary', app(CartService::class)->summary());
        });
    }
}
