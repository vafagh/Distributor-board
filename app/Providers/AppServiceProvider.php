<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use DB;

class AppServiceProvider extends ServiceProvider
{
    /**
    * Bootstrap any application services.
    *
    * @return void
    */
    public function boot()
    {
        Schema::defaultStringLength(191);

        view()->composer(['home','backorder','layouts.menu','map','layouts.components.create.service','layouts.components.create.fillup','layouts.components.edit.service'], function ($view)
        {
            $activeDrivers = \App\Driver::where('truck_id','!=',null) //with('rides','rides.truck','rides.rideable')
                    ->get();
            $today = new Carbon();
            $tomorrow = new Carbon('tomorrow');
            $dates = array(
                'today' => $today->format('Y-m-d'),
                'tomorrow' => $tomorrow->format('Y-m-d'),
                'firstDayOfWeek' => Carbon::now()->startOfWeek()->format('Y-m-d'),
                'firstDayOfMonth' => Carbon::now()->startOfMonth()->format('Y-m-d')
            );
            $view->with(compact('activeDrivers','dates'));
        });
        view()->composer(['layouts.components.create.rideable','layouts.components.edit.rideable'], function ($view)
        {
            $allwarehouse = \App\Location::where('type','!=','Client')->where('name','!=','Other')->orderBy('name')->get();
            $view->with(compact('allwarehouse'));
        });

        // DB::listen(function ($query) {
        //     var_dump([
        //         $query->sql,
        //         // $query->bindings,
        //         $query->time
        //     ]);
        // });

    }

    /**
    * Register any application services.
    *
    * @return void
    */
    public function register()
    {
        //
    }
}
