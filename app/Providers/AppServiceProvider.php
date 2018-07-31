<?php

namespace App\Providers;

use App\Http\Service\AuthService;
use App\Http\Service\ClickService;
use App\Http\Service\FilterService;
use App\Http\Service\ProductService;
use App\Http\Service\RateService;
use App\Http\Service\TransactionService;
use App\Http\Service\Utilities;
use Illuminate\Support\ServiceProvider;
use Monolog\Handler\SlackHandler;
use Monolog\Logger;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $monolog = \Log::getMonolog();
        if(\App::environment('production')) {
            $slackHandler = new SlackHandler(\Config::get('constant.auth.token'), \Config::get('constant.auth.channel.product'), 'Rongdo Post Server', true, null, Logger::ERROR);
            $monolog->pushHandler($slackHandler);
        }
        elseif(\App::environment('staging')) {
            $slackHandler = new SlackHandler(\Config::get('constant.auth.token'), \Config::get('constant.auth.channel.staging'), 'Paledev Server', true, null, Logger::ERROR);
            $monolog->pushHandler($slackHandler);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->bind('FilterService', function() {
            return new FilterService();
        });
        $this->app->bind('ProductService', function() {
            return new ProductService();
        });
        $this->app->bind('TransactionService', function() {
            return new TransactionService();
        });
        $this->app->bind('RateService', function() {
            return new RateService();
        });
        $this->app->bind('Utilities', function() {
            return new Utilities();
        });
        $this->app->bind('AuthService', function() {
            return new AuthService();
        });
        $this->app->bind('ClickService', function() {
            return new ClickService();
        });
    }
}
