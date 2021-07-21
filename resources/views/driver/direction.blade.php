@extends('layouts.app')
@section('content')

    @section('metas')
        <style>
            #right-panel select, #right-panel input {
                font-size: 15px;
            }

            #right-panel select {
                width: 100%;
            }

            #map {
                height: 100%;
                width: 100%;
                height: 100%;
            }
            #right-panel {
                height: 400px;
            }
        </style>
    @endsection
    <div class="card">

        @component('driver.header',['driver'=>$driver])
        @endcomponent

        <div class="card-body p-0">
            <div class="row">
                <div class="col-8">
                    <div id="map" class='position-absolute'></div>
                </div>
                <div class="col-4">
                    <div id="right-panel">
                        <div>

                            <input type=hidden id="start" value="1628 E Main Street, Grand Prairie, TX 75050">
                            <br><b>Waypoints:</b> <br>
                            <i>(Ctrl+Click for multiple selection)</i> <br>
                            <select class="custom-select" multiple id="waypoints" size="{{$locations->count()}}">
                                @php $latsum =0;$lngsum=0; $stopcount=0; @endphp
                                @foreach ($locations as $key => $location)
                                    <option selected value="{{$location->line1.' '.$location->city.' '.$location->state.' '.$location->zip}}">{{$location->longName}}</option>
                                    @php
                                    $latsum = $latsum + $location->lat;
                                    $lngsum = $lngsum + $location->lng;
                                    $stopcount++;
                                    @endphp
                                @endforeach
                            </select>
                            <input type="submit" id="submit">
                        </div>
                    </div>
                </div>
            </div>


            <div class="list-group mt-4">
                <div class="row  border-buttom-1 border-primary">
                    <div class="h5 col-1">Route</div>
                    <div class="d-flex w-100 justify-content-between col-9">
                            <div class="col-6">From</div>
                            <div class="col-6">To</div>
                    </div>
                    <div  class="col-1">Duration</div>
                    <div  class="col-1">Distance</div>
                </div>
            </div>


            <div id="directions-panel"></div>
            <div class="list-group mt-4">
                <div class="row  border-buttom-1 border-primary">
                    <div class="h5 col-1"></div>
                    <div class="col-9 h4 text-right">
                        Total
                    </div>
                    <div  class="col-1"><span id='shiftTotalDuration'></span></div>
                    <div  class="col-1"><span id='shiftTotalDistance'></span></div>
                </div>
                <div class="row px-4 border-buttom-1 border-primary">
                    <div class="h5 col-6 text-right" >Totat Rides + Total stops duration: </div>
                    <div class="h4 col-6 text-left"  id="totalETA"></div>
                </div>
                <div class="row px-4 border-buttom-1 border-primary">
                    <div class="h5 col-6 text-right" >If driver leaving by {{$shift=="Morning"?"9:00am":"1:00pm"}} should be back by:</div>
                    <div class="h4 col-6 text-left"  id="returnTime"></div>
                </div>
            </div>

        </div>
    </div>

            @component('rideable.lines',
            [
                'driver'=>$driver,
                'ongoingRides' => $ongoingRides,
                'finishedRides' => false,
                'defaultPickups' => false,
                'currentUnassign' => false,
                'print' => true
            ])
            @endcomponent
    <script>
    function initMap() {
        var directionsService = new google.maps.DirectionsService;
        var directionsDisplay = new google.maps.DirectionsRenderer;
        var map = new google.maps.Map(document.getElementById('map'), {
            zoom: 6,
            center: {lat: {{$latsum/$stopcount}}, lng: {{$lngsum/$stopcount}}}
        });
        function run() {
            calculateAndDisplayRoute(directionsService, directionsDisplay);
        }
        directionsDisplay.setMap(map);
        run();
        document.getElementById('submit').addEventListener('click', run);

    }

    function calculateAndDisplayRoute(directionsService, directionsDisplay) {
        var waypts = [];
        var checkboxArray = document.getElementById('waypoints');
        for (var i = 0; i < checkboxArray.length; i++) {
            if (checkboxArray.options[i].selected) {
                waypts.push({
                    location: checkboxArray[i].value,
                    stopover: true
                });
            }
        }

        directionsService.route({
            origin: document.getElementById('start').value,
            destination: document.getElementById('start').value,
            waypoints: waypts,
            optimizeWaypoints: true,
            travelMode: 'DRIVING'
        }, function(response, status) {
            if (status === 'OK') {
                directionsDisplay.setDirections(response);
                var route = response.routes[0];
                var summaryPanel = document.getElementById('directions-panel');
                summaryPanel.innerHTML = '';
                // For each route, display summary information.
                var shiftTotalDuration = 0;
                var shiftTotalDistance = 0;
                for (var i = 0; i < route.legs.length; i++) {
                    var routeSegment = i + 1;
                    shiftTotalDuration += route.legs[i].duration.value;
                    shiftTotalDistance += route.legs[i].distance.value;
                    summaryPanel.innerHTML += '<div class="list-group">'+
                        '<div class="row">'+
                            '<div class="h5 col-1">' + routeSegment + '</div>'+
                            '<div class="d-flex w-100 justify-content-between col-9">'+
                                    '<div class="col-6">' + route.legs[i].start_address + '</div>'+
                                    '<div class="col-6">' + route.legs[i].end_address + '</div>'+
                            '</div>'+
                            '<div  class="col-1">' + route.legs[i].duration.text + '</div>'+
                            '<div  class="col-1">' + route.legs[i].distance.text + '</div>'+
                        '</div>'+
                        '</div>';
                }
                function timeConvert(n) {
                    var num = n;
                    var hours = (num / 60);
                    var rhours = Math.floor(hours);
                    var minutes = (hours - rhours) * 60;
                    var rminutes = Math.round(minutes);
                    return rhours + " : " + rminutes ;
                    }
                document.getElementById("shiftTotalDuration").innerHTML=timeConvert(shiftTotalDuration/60);
                document.getElementById("totalETA").innerHTML=timeConvert(shiftTotalDuration/60+({{$locations->count()}}*10));
                document.getElementById("shiftTotalDistance").innerHTML=(shiftTotalDistance/1609.34).toFixed(2);
                document.getElementById("returnTime").innerHTML=timeConvert(shiftTotalDuration/60+({{$locations->count()}}*10)+{{$shift=="Morning"?9*60:60}}) ;
            } else {
                window.alert('Directions request failed due to ' + status);
            }
        });
    }
    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key={{env('GOOGLE_MAP_API')}}&callback=initMap">
    </script>

@endsection
