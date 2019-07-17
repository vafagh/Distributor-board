<div class="card-body">
    @if ($defaultPickups && $defaultPickups->count()>0)
        <div class="card-header">
            <span>
                <i class="material-icons md-1">business</i>
                Assigned to pickup from:
            </span>
            @foreach ($defaultPickups as $location)
                @component('layouts.components.tooltip',['modelName'=>'location','model'=>$location])
                @endcomponent,
            @endforeach
        </div>
    @endif

    <div class="card-header  m-0 justify-around">
        @if ($currentUnassign && $currentUnassign->count()>0 && $driver->truck_id != null)

            <i class="material-icons md-1">store_mall_directory</i>
            <span class="btn btn-sm ml-4">Un-assigned deliveries:</span>

            @foreach ($currentUnassign as $rideable)
                <div class="add d-inline">
                    <a class="btn btn-{{$rideable->type=='Warehouse'?'success':'info'}}{{(Auth::user()->role_id>3 && $rideable->type=='DropOff') ?' d-none':''}} btn-sm text-white h-75" title="{{$rideable->location->name}}" href="/ride/store/{{$rideable->id}}/{{$driver->id}}">
                        <i class="material-icons md-14 text-dark">add</i>
                        {{$rideable->invoice_number}}

                    </a>
                </div>
            @endforeach

            @if (empty($date))
                <div class="add d-inline"><a class="btn btn-danger{{(Auth::user()->role_id==3 && $rideable->type!='Client') ?' d-none':''}} btn-sm text-white h-75" title="See other days" href="?date=all"><i class="material-icons md-14 text-dark">calendar_today</i>More</a></div>
            @else
                <div class="add d-inline"><a class="btn btn-danger{{(Auth::user()->role_id==3 && $rideable->type!='Client') ?' d-none':''}} btn-sm text-white h-75" title="Only Today's" href="?date="><i class="material-icons md-14 text-dark">today</i>Less</a></div>
            @endif
        @else
            <div class="">
                There is not any unassign ticket to display!
                <div class="add d-inline"><a class="btn btn-danger{{(Auth::user()->role_id==3 && $rideable->type!='Client') ?' d-none':''}} btn-sm text-white h-75" title="See other days" href="?date=all"><i class="material-icons md-14 text-dark">calendar_today</i>Other day</a></div>
            </div>

        @endif
        @component('layouts.components.modal',['modelName'=>'rideable','action'=>'create','iterator'=>0,'object'=>null,'op1'=>'Client','op2'=>'Delivery','style'=>'bg-light','dingbats'=>'<i class="material-icons">add_box</i>','autocomplateOff'=>true])
        @endcomponent
    </div>

    <div class="card-body p-0">
        <table class="table table-compact">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>For</th>
                    <th class="mw-100">#</th>
                    <th>Status</th>
                    <th>Truck</th>
                    <th>Schaduled for</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ongoingRides as $key => $ride)
                    <tr>
                        <td class='pl-2'>{{$ride->id}}</td>
                        @if (!empty($ride->rideable))
                            <td class="location text-truncate">
                                @component('layouts.components.tooltip',['modelName'=>'location','model'=>$ride->rideable->location])
                                @endcomponent
                            </td>
                            <td class="invoice fixedWidthFont font-weight-bold h4 minw-200">
                                @if ((Auth::user()->role_id > 3 || Auth::user()->id == $ride->rideable->user_id) )
                                    @component('layouts.components.modal',['modelName'=>'rideable','action'=>'edit','dingbats'=>'<i class="material-icons md-16">border_color</i>','style'=>'badge badge-warning mr-1 float-left','iterator'=>$key,'object'=>$ride->rideable,'op1'=>$ride->rideable->type,'op2'=>'','file'=>false,'autocomplateOff'=>true])
                                    @endcomponent
                                @endif
                                @component('layouts.components.tooltip',['modelName'=>'rideable','model'=>$ride->rideable])
                                @endcomponent
                                @if (Auth::user()->role_id > 3 && $ride->rideable->status!='Done' && $ride->rideable->status!='Returned' && $ride->rideable->status!='Return')
                                    <a title="Part Delivered" class="badge badge-primary" href="/rideable/{{$ride->rideable->id}}/Done"><i class="material-icons md-16">done</i></a>
                                @endif
                            </td>
                            <td>{{$ride->rideable->status}}</td>
                        @else
                            <td colspan="3">
                                The ticket is deleted.
                                @if (Auth::user()->role_id>3)
                                    <a href="/ride/delete/{{$ride->id}}">remove this line</a>
                                @endif
                            </td>
                        @endif
                        <td>
                            @component('layouts.components.tooltip',['modelName'=>'truck','model'=>$ride->truck])
                            @endcomponent
                        </td>
                        <td>
                            <div title="{{$ride->created_at->diffForHumans()}}">
                                {{-- {{$ride->created_at->toFormattedDateString()}}
                                <span class="text-muted font-weight-light">{{$ride->created_at->toTimeString()}}</span> --}}
                                @if(!empty($ride->rideable) && $ride->rideable->location->type != 'DropOff' && $ride->rideable->delivery_date!=null)
                                    <span title="{{$ride->rideable->delivery_date.' '.$ride->rideable->shift}}">
                                        <i class="material-icons small">send</i>
                                        @if(App\User::setting('humanDate') && Auth::user()->role_id!=3)
                                            {{ App\Helper::dateName($ride->rideable->delivery_date)}}
                                        @else
                                            {{$ride->rideable->delivery_date}}
                                        @endif
                                        <i class="material-icons small">schedule</i>
                                        <span class="text-muted font-weight-light">{{ $ride->rideable->shift}}</span>
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if (Auth::user()->role_id >= 3 && $ride->rideable != null && $ride->rideable->status!='Done' && $ride->rideable->status!='Returned' && $ride->rideable->status!='Return')
                                <a title="{{$driver->fname}} didn't went to {{$ride->rideable->location->name}}" class="badge badge-danger" href="/ride/detach/{{$ride->id}}/{{$ride->rideable->id}}">
                                    <i class="material-icons md-18">link_off</i>
                                    {{-- Detach --}}
                                </a>
                                @if (Auth::user()->role_id > 3 )
                                    <a title="{{$driver->fname}} went to {{$ride->rideable->location->name}} but the part rerurned to eagle" class="badge badge-danger" href="/rideable/{{$ride->rideable->id}}/Returned"><i class="material-icons md-18">keyboard_return</i></a>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
                @if ($finishedRides)
                    <tr class="table-dangers">
                        <td colspan="7 bg-black">
                            <div class="d-inline-block pagination-sm">
                                {{ $finishedRides->links("pagination::bootstrap-4") }}
                            </div>
                        </td>
                    </tr>
                    @foreach ($finishedRides as $key => $ride)
                        <tr>
                            <td class='pl-2'>{{$ride->id}}</td>
                            @if (!empty($ride->rideable))
                                <td class="location text-truncate">
                                    @component('layouts.components.tooltip',['modelName'=>'location','model'=>$ride->rideable->location])
                                    @endcomponent
                                </td>
                                <td class="invoice fixedWidthFont font-weight-bold h4 minw-200">
                                    @if (Auth::user()->role_id > 3 || Auth::user()->id == $ride->rideable->user_id )
                                        @component('layouts.components.modal',['modelName'=>'rideable','action'=>'edit','dingbats'=>'<i class="material-icons md-16">border_color</i>','style'=>'badge badge-warning mr-1 float-left','iterator'=>$key,'object'=>$ride->rideable,'op1'=>$ride->rideable->type,'op2'=>'','file'=>false,'autocomplateOff'=>true])
                                        @endcomponent
                                    @endif
                                    @component('layouts.components.tooltip',['modelName'=>'rideable','model'=>$ride->rideable])
                                    @endcomponent
                                    @if (Auth::user()->role_id > 3 && $ride->rideable->status!='Done' && $ride->rideable->status!='Returned' && $ride->rideable->status!='Return')
                                        <a class="badge badge-primary" href="/rideable/{{$ride->rideable->id}}/Done">&#x2714; Done</a>
                                    @endif
                                </td>
                                <td>{{$ride->rideable->status}}</td>
                            @else
                                <td class='no-info' colspan="3">
                                    The ticket is deleted.
                                    @if (Auth::user()->role_id>3)
                                        <a href="/ride/delete/{{$ride->id}}">remove this line</a>
                                    @endif
                                </td>
                            @endif
                            <td class="truck text-truncate">
                                @component('layouts.components.tooltip',['modelName'=>'truck','model'=>$ride->truck])
                                @endcomponent
                            </td>
                            <td>
                                <div title="{{$ride->created_at->diffForHumans()}}">
                                    {{-- {{$ride->created_at->toFormattedDateString()}}
                                    <span class="text-muted font-weight-light">{{$ride->created_at->toTimeString()}}</span> --}}
                                    @if(!empty($ride->rideable) && $ride->rideable->location->type != 'DropOff' && $ride->rideable->delivery_date!=null)
                                        <span title="{{$ride->rideable->delivery_date.' '.$ride->rideable->shift}}">
                                            <i class="material-icons small">send</i>
                                            @if(App\User::setting('humanDate') && Auth::user()->role_id!=3)
                                                {{ App\Helper::dateName($ride->rideable->delivery_date)}}
                                            @else
                                                {{$ride->rideable->delivery_date}}
                                            @endif
                                            <i class="material-icons small">schedule</i>
                                            <span class="text-muted font-weight-light">{{ $ride->rideable->shift}}</span>
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if (Auth::user()->role_id >= 3 && $ride->rideable != null && $ride->rideable->status!='Done' && $ride->rideable->status!='Returned' && $ride->rideable->status!='Return')
                                    <a title="Remove driver from this invoice" class="badge badge-danger" href="/ride/detach/{{$ride->id}}/{{$ride->rideable->id}}">Detach</a>
                                    @if (Auth::user()->role_id > 3 )
                                        <a title="Returned" class="badge badge-danger" href="/rideable/{{$ride->rideable->id}}/Returned"><i class="material-icons md-14">keyboard_return</i></a>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="6">
                            <div class="pagination">
                                {{ $finishedRides->links("pagination::bootstrap-4") }}
                            </div>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>