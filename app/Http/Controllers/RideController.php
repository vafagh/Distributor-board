<?php

namespace App\Http\Controllers;

use Auth;
use App\Ride;
use App\Rideable;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class RideController extends Controller
{
    public function index()
    {
        $rides = Ride::with('rideable','rideable.location','driver','truck')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('ride.rides',compact('rides'));
    }

    public function create(Rideable $rideable)
    {
        return view('ride.create',compact('rideable'));
    }

    public function attach(Request $request)
    {
        $ride = new Ride;
        $ride->rideable_id = $request->id;
        $ride->truck_id = $request->truck;
        $ride->driver_id = $request->driver;
        $ride->distance = $request->distance;
        $ride->save();

        $rideable=Rideable::find($request->id);
        $rideable->status = 'OnTheWay';
        $rideable->save();
        $rideable->rides()->attach($ride->id);
        Transaction::log(Route::getCurrentRoute()->getName(),Ride::find($request->id),$ride);

        return redirect('/#rideable'.$ride->rideable_id)->with('status', 'Driver Assigned');

    }

    public function detach($ride_id,$rideable_id, Request $request)
    {
        $rideable=Rideable::find($rideable_id);
        $rideable->status = 'Created';
        $rideable->save();
        if($ride_id > 0){
            $rideable->rides()->detach($ride_id);
            Ride::destroy($ride_id);
        }else {
            return redirect('/')->with('error', 'Ride not found!');
        }
        Transaction::log(Route::getCurrentRoute()->getName(),Ride::find($request->id),$ride);

        return redirect('/')->with('status', 'Driver dismissed from this task');
    }

    public function edit(Ride $ride)
    {
        return view('edit');
    }

    public function update(Request $request)
    {
        $ride = Ride::find($request->id);
        $ride->driver_id = $request->driver;
        $ride->truck_id = $request->truck;
        $ride->save();
        Transaction::log(Route::getCurrentRoute()->getName(),Ride::find($request->id),$ride);

        return redirect('/rides')->with('status', 'Ride Updated!');
    }

    public function destroy(Ride $ride)
    {
        Ride::destroy($ride->id);
        Transaction::log(Route::getCurrentRoute()->getName(),$ride,false);

        return redirect('/rides')->with('status', 'Ride Destroid!');
    }
}
