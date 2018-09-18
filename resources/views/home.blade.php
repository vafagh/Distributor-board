@extends('layouts.app')

@section('content')
    {{-- <div class="card">
    <div class="card-body">
    @component('rideable.rideable',['collection'=> $deliveries,'op1'=>'Client','op2'=>'Delivery','flashId'=>$flashId])
    File Missing!
@endcomponent
<hr>
@component('rideable.rideable',['collection'=> $pickups,'op1'=>'Warehouse','op2'=>'Pickup','flashId'=>$flashId])
File Missing!
@endcomponent
</div>
</div> --}}

<div class="locations card">
    <div class="card-header">Pickups by locations</div>
    <div class="card-body">
        <div class="row d-flex justify-content-around">
            @foreach ($warehouses as $key => $warehouse)
                <div class="card col-6 col-sm-4 col-md-3 col-lg-2 col-xl-1 px-0">
                    <div class="card-header text-center mh-20 px-0 py-1 ">
                        @component('layouts.components.tooltip',
                        ['modelName'=>'location','model'=>$warehouse])@endcomponent
                    </div>

                    <div class="card-body px-1">
                        <p class="card-text">
                            <small class="text-muted">
                                Total trip :{{ App\Rideable::where('location_id', $warehouse->id)->count() }}
                            </small>
                            @foreach (App\Rideable::where([
                                ['status','!=','Done'],
                                ['status','!=','Canceled'],
                                ['status','!=','Delete'],
                                ['location_id',$warehouse->id]
                                ])->get() as $key => $value)
                                <div class="fixedWidthFont">
                                    {{$value->invoice_number}}
                                </div>

                            @endforeach
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <div class="card-footer">

    </div>
</div>
<div class="map card mt-3">
    <div class="card-header">All ongoing rides</div>
    <div class="card-body" style="height:430px">


          <div id="default" style="width:100%; height:100%"></div>
        @section('footer-scripts')

            <script type="text/javascript">
            var locations = [
                @foreach ($rideables as $key => $rideable)
                    [@if (!empty($rideable->location->lat))"{{$rideable->location->name}}",{{$rideable->location->lat}},{{$rideable->location->lng}},"{{$rideable->id}}","{{$rideable->rides->count()}}","{{(empty($rideable->rides->first()->driver)) ? "notAssigned".$rideable->type : $rideable->rides->first()->driver->fname.$rideable->type }}","{{$rideable->type}}","{{$rideable->location->line1." ".$rideable->location->city}}" @endif],
                @endforeach
            ];
            var iconBase = "/img/driver/small/";
            var icons = {
                @foreach ($activeDrivers as $key => $driver)
                "{{$driver->fname}}Warehouse": {"icon": "/img/driver/small/{{strtolower($driver->fname)}}Warehouse.png"},
                    "{{$driver->fname}}Client": {"icon": "/img/driver/small/{{strtolower($driver->fname)}}Client.png"},
                @endforeach
                    "notAssigned": {"icon": "/img/driver/small/notAssigned.png"},
                    "notAssignedWarehouse": {"icon": "/img/driver/small/notAssignedWarehouse.png"},
                    "notAssignedClient": {"icon": "/img/driver/small/notAssignedClient.png"},
                    "notAssignedDropOff": {"icon": "/img/driver/small/notAssignedDropOff.png"}
            }
            function initialize() {
                var myOptions = {
                    center: new google.maps.LatLng({{env('STORE_LAT')}}, {{env('STORE_LNG')}}),
                    zoom: 10,
                    scaleControl: true,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                };
                var map = new google.maps.Map(document.getElementById("default"),
                myOptions);
                setMarkers(map,locations);

                var simplePoly = [
                    new google.maps.LatLng({{env('STORE_LAT')}}, {{env('STORE_LNG')}}),
                    new google.maps.LatLng({{env('STORE_LAT')}}, {{env('STORE_LNG')}})
                ];
                var flightPath = new google.maps.Polyline({
                    path: simplePoly,
                    editable: true,
                    strokeColor: '#FF0000',
                    strokeOpacity: 1.0,
                    strokeWeight: 2,
                    map: map
                });
            }
            function setMarkers(map,locations){
                var marker, i
                for (i = 0; i < locations.length; i++)
                {
                    var store = locations[i][0]
                    var lat = locations[i][1]
                    var long = locations[i][2]
                    var rideable_id =  locations[i][3]
                    var ridesCount =  locations[i][4]
                    var driver =  locations[i][5]
                    // var type =  locations[i][6]
                    var add =  locations[i][7]
                    latlngset = new google.maps.LatLng(lat, long);
                    var marker = new google.maps.Marker({
                        map: map,
                        title: store,
                        position: latlngset,
                        icon: icons[driver].icon
                        // label: i
                    });

                    map.setCenter(marker.getPosition())

                    var content = "<h4>" + store + '</h4>'+
                        add+'<br>'+
                        '<strong>'+driver+'</strong><br>'+
                        'Assign it to:<br>'
                    @foreach ($activeDrivers as $key => $driver)
                        +"<a href='/ride/store/"+rideable_id+"/{{$driver->id}}'>{{$driver->fname}}</a><br>"
                    @endforeach

                    var infowindow = new google.maps.InfoWindow()
                    google.maps.event.addListener(marker,'click',(function(marker,content,infowindow){
                        return function() {
                            infowindow.setContent(content);
                            infowindow.open(map,marker);
                        };
                    })(marker,content,infowindow));
                }
            }
            </script>
            <script async defer src="https://maps.googleapis.com/maps/api/js?key={{env('GOOGLE_MAP_API')}}"></script>

        @endsection
    </div>
</div>
@endsection
