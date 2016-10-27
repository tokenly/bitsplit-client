<?php

namespace Tokenly\BitsplitClient;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

/*
* TokenDeliveryServiceProvider
*/
class BitsplitServiceProvider extends ServiceProvider
{

    public function boot()
    {
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->bindConfig();

        $this->app->bind('Tokenly\BitsplitClient\Client', function($app) {
            $client = new \Tokenly\BitsplitClient\Client(Config::get('bitsplit.host'), Config::get('bitsplit.key'), Config::get('bitsplit.secret'));
            return $client;
        });

    }

    protected function bindConfig()
    {
        // simple config
        $config = [
            'bitsplit.host' => env('BITSPLIT_HOST', 'https://bitsplit.tokenly.com'),
            'bitsplit.key'      => env('BITSPLIT_KEY'     , null),
            'bitsplit.secret'        => env('BITSPLIT_SECRET'       , null),
        ];

        // set the laravel config
        Config::set($config);

        return $config;
    }

}

