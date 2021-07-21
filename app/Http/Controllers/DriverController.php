<?php
namespace App\Http\Controllers;

use App\Ride;
use App\Driver;
use App\Helper;
use App\Location;
use App\Rideable;
use Carbon\Carbon;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class DriverController extends Controller
{

    public function index()
    {
        $drivers = Driver::with('rides','fillups','rides.truck','rides.rideable.location')
                ->orderByDesc('working')
                ->orderBy('truck_id')
                ->get();

        return view('driver.drivers',compact('drivers'));
    }

    public function store(Request $request)
    {
        $driver = new Driver;
        $driver->fname = $request->fname;
        $driver->lname = $request->lname;
        $driver->phone = $request->phone;
        $driver->color = $request->color;
        $driver->email = $request->email;
        ($request->truck == 'clear') ? $driver->truck_id = null : $driver->truck_id = $request->truck;
        if($request->file('avatar')!=NULL){
            $image = time().'.'. $request->file('avatar')->getClientOriginalExtension();
            $request->file('avatar')->move(public_path('img/driver'), $image);
            $driver->image = $image;
        }
        $driver->save();

        Transaction::log(Route::getCurrentRoute()->getName(),'',$driver);

        return redirect('/drivers/')->with('status', $driver->fname." added!");
    }

    public function show($driver_id, Request $request)
    {
        $driver = Driver::with('rides','fillups','rides.truck','rides.rideable','rides.rideable.location','services')->find($driver_id);
        (empty($request->input('sortby'))) ? $rideSort = 'created_at': $rideSort = $request->input('sortby');
        $ongoingRides = $driver->rides()
            ->whereHas('rideable', function($q) {
                $q->whereNotIn('status',['Reschedule','Done','Returned','NotAvailable','Pulled','NoData','Return','Closed','Nobody','NoMoney','Canceled','NoTicket','NoTime','Damaged','Wrong']);
            })
            ->orderBy('created_at','desc')
            ->get();

        $finishedRides = $driver->rides()
            ->whereHas('rideable', function($q) {
                $q->where('status', '!=', 'OnTheWay');
            })
            ->orderBy('created_at','desc')
            ->paginate(10);
        $today = Carbon::today()->toDateString();

        $shiftOperator = '=';
        $dayOperator = '=';
        if($request->input('date')=='all'){
            $day = -1;
            $shift = -1;
            $shiftOperator = '!=';
            $dayOperator = '!=';
        }elseif(empty($request->input('date')) || empty($request->input('shift'))){
            $day = $today;
            $shift = date('H')>13 ? 'Evening' : 'Morning';
        }
        else{
            $day = $request->input('date');
            $shift = $request->input('shift');
        }
        // dd($day);
        $currentUnassign = Rideable::whereIn('status', ['Created','DriverDetached','Reschedule','Ready'])
            ->whereDoesntHave('location', function($q) {
                $q->whereIn('name', ['IND','Online','Other']);
            })
            ->where('delivery_date', $dayOperator, $day)
            ->where('shift', $shiftOperator, $shift)
            ->get();

        $unassignLocations = $currentUnassign->pluck('location')->flatten()->unique()->sortBy('name');
        $defaultPickups = Location::where('driver_id',$driver_id)->get();

        return view('driver.show',compact('driver', 'ongoingRides', 'finishedRides', 'rideSort', 'currentUnassign','unassignLocations','defaultPickups','request','today'));
    }

    public function update(Request $request)
    {
        try{

            $driver = Driver::find($request->id);
            $driver->fname = $request->fname;
            $driver->lname = $request->lname;
            $driver->phone = $request->phone;
            $driver->email = $request->email;
            $driver->color = $request->color;
            ($request->truck == 'clear') ?  $driver->truck_id=null : $driver->truck_id = $request->truck;
            if($request->working=='on'){
                $driver->working = true;
            } else {
                $driver->working = false ;
                $driver->truck_id = NULL;
            }
            if($request->file('avatar')!=NULL){
                $image = time().'.'. $request->file('avatar')->getClientOriginalExtension();
                $request->file('avatar')->move(public_path('img/driver'), $image);
                $driver->image = $image;
            }

            Transaction::log(Route::getCurrentRoute()->getName(),Driver::find($request->id),$driver);

            $driver->save();

            return back()->with('status', $driver->fname." Updated!");
        }
        catch(\Exception $e){
            return back()->with('error', $e);
        }
    }

    public function unassign(Driver $driver)
    {
        $old_record = Driver::find($driver->id);
        $driver->truck_id = NULL;
        $driver->save();
        Transaction::log(Route::getCurrentRoute()->getName(),$old_record,$driver);

        return redirect('/drivers/')->with('status', $driver->fname." Updated!");
    }

    public function destroy(Request $request,Driver $driver)
    {
        Driver::destroy($driver->id);
        Transaction::log(Route::getCurrentRoute()->getName(),$driver,false);

        return redirect('drivers')->with('status', $driver->fname." Deleted!");
    }

    public function direction(Driver $driver, $history, $shift)
    {
        if (empty($history) || $history =='today') {
            $today = new Carbon();
            $history = $today->format('Y-m-d');
        }
        $hisexp = explode('-', $history);
        $dt = Carbon::create($hisexp[0],$hisexp[1],$hisexp[2],0 ,0,0,'America/Chicago');
        $inFunVar = [$driver->id, $history, $shift];
        $locations = Location::whereHas('rideables.rides', function($q) use($inFunVar){
                                    $q->where([
                                        ['driver_id', '=', $inFunVar[0]],
                                        ['delivery_date','=',$inFunVar[1]],
                                        ['shift','=',$inFunVar[2]]
                                    ]);
                                })
                                ->get();
        if ($locations->count()<1) {
            return back()->with('error', $driver->fname.' does not assigned for any ride on '.$history);
        }

        $ongoingRides = $driver->rides()
                                ->whereHas('rideable', function($q) use ($inFunVar) {
                                    $q->where([
                                        ['delivery_date','=',$inFunVar[1]],
                                        ['shift','=',$inFunVar[2]]
                                    ]);
                                })
                                ->orderBy('created_at','desc')
                                ->get();

        return view('driver.direction',compact('driver', 'locations','dt','ongoingRides','shift'));
    }
}
