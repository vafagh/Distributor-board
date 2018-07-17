<?php

namespace App\Http\Controllers;

use App\Truck;
use Illuminate\Http\Request;

class TruckController extends Controller
{
    public function index()
    {
        $trucks = Truck::with('rides','fillups')
            // ->where('','')
            ->orderBy('id', 'desc')
            ->get();

            return view('trucks',compact('trucks'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $track = new Truck;
        $track->license_plate = $request->license_plate;
        $track->gas_card = $request->gas_card;
        $track->tank_capacity = $request->tank_capacity;
        $track->last4vin = $request->last4vin;
        $track->lable = $request->lable;
        $track->save();
        return redirect('/trucks/')->with('status', $track->license_plate." added!");

    }

    public function show(Truck $truck)
    {
        //
    }

    public function edit(Truck $truck)
    {
        //
    }

    public function update(Request $request)
    {
        $track = Truck::find($request->id);
        $track->license_plate = $request->license_plate;
        $track->gas_card = $request->gas_card;
        $track->tank_capacity = $request->tank_capacity;
        $track->last4vin = $request->last4vin;
        $track->lable = $request->lable;
        if($request->file('image')!=NULL){
            $image = time().'.'. $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(public_path('img/trucks'), $image);
            $track->image = $image;
        }
        $track->save();

        return redirect('/trucks/')->with('status', $track->license_plate." Updated!");
    }

    public function destroy(Truck $truck)
    {
        //
    }
}
