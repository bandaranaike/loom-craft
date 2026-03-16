<?php

use App\Models\ExchangeRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('stores the latest paypal exchange rate snapshot', function () {
    Http::fake([
        'https://open.er-api.com/v6/latest/USD' => Http::response([
            'result' => 'success',
            'time_last_update_unix' => now()->subHour()->timestamp,
            'rates' => [
                'LKR' => 300,
            ],
        ]),
    ]);

    $this->artisan('commerce:sync-exchange-rates')
        ->assertSuccessful();

    $rate = ExchangeRate::query()->firstOrFail();

    expect($rate->from_currency)->toBe('LKR')
        ->and($rate->to_currency)->toBe('USD')
        ->and($rate->rate)->toBe('0.00333333')
        ->and($rate->source)->toBe('open_er_api');
});
