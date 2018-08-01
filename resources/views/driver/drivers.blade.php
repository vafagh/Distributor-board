@extends('layouts.app')
@section('content')
    <div class="">
        <div class="card">
            <div class="card-header row m-0 bg-primary text-white">
                <div class="col-10 h3 active">Drivers</div>
                <div class="col-2">
                    @component('layouts.components.modal',[
                        'modelName'=>'driver',
                        'action'=>'create',
                        'object'=>null,
                        'op1'=>'op1',
                        'op2'=>'driver',
                        'iterator'=>0,
                        'file'=>true])
                    @endcomponent
                </div>
            </div>
            <div class="card-body">
                @component('driver.driverslist',['drivers'=> $drivers])
                    File Missing!
                @endcomponent
            </div>
        </div>
    </div>
@endsection
