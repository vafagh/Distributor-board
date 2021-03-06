<?php

namespace App\Http\Controllers;

use App\Fillup;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class FillupController extends Controller
{
    public function index()
    {
        $fillups = Fillup::with('driver','truck')
        ->orderBy('id', 'desc')
        ->get();

        return view('fillup.fillups',['fillups'=>$fillups]);
    }


    public function show(Fillup $fillup)
    {
        $fillup = Fillup::with('driver','truck')
        ->find($fillup->id);

        return view('fillup.show',['fillup'=>$fillup]);
    }


    public function store(Request $request)
    {
        $fillup = new Fillup;
        $fillup->truck_id = $request->truck_id;
        $fillup->driver_id = $request->driver_id;
        $fillup->gas_card = $request->gas_card;
        $fillup->gallons = $request->gallons;
        $fillup->product = $request->product;
        $fillup->price_per_gallon = $request->price_per_gallon;
        $fillup->total = $request->total;
        $fillup->mileage = $request->mileage;
        if($request->file('image')!=NULL){
            $image = time().'.'. $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(public_path('img/fillup'), $image);
            $fillup->image = $image;
        }
        $fillup->save();

        Transaction::log(Route::getCurrentRoute()->getName(),'',$fillup);

        return redirect()->back();
    }

    public function update(Request $request)
    {
        $fillup = Fillup::find($request->id);
        $fillup->truck_id = $request->truck_id;
        $fillup->driver_id = $request->driver_id;
        $fillup->gas_card = $request->gas_card;
        $fillup->gallons = $request->gallons;
        $fillup->product = $request->product;
        $fillup->price_per_gallon = $request->price_per_gallon;
        $fillup->total = $request->total;
        $fillup->mileage = $request->mileage;
        if($request->file('image')!=NULL){
            $image = time().'.'. $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(public_path('img/fillup'), $image);
            $fillup->image = $image;
        }
        $fillup->save();

        Transaction::log(Route::getCurrentRoute()->getName(),Fillup::find($request->id),$fillup);

        return redirect()->back();
    }

    public function destroy(Request $request,Fillup $fillup)
    {
        Fillup::destroy($fillup->id);
        Transaction::log(Route::getCurrentRoute()->getName(),$fillup,false);
        return redirect()->back()->with('status', 'Record is Destroid!');
    }
}
