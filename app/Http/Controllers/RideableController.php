<?php

namespace App\Http\Controllers;

use DateTime;
use Auth;
use App\Ride;
use App\Helper;
use App\Driver;
use App\Location;
use App\Rideable;
use App\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class RideableController extends Controller
{
    public function list(Request $request, $type)
    {
        if($type == "delivery") { $op1 = 'Client';    $op2 = 'Delivery'; $operator = '=';  $orderColumn = 'created_at'; }
        else                    { $op1 = 'Warehouse'; $op2 = 'Pickup';   $operator = '!='; $orderColumn = 'location_id';}

        (empty($request->input('sortby'))) ? $rideableSort = $orderColumn: $rideableSort = $request->input('sortby');

        if($request->filled('status')){
            $field2Name = 'status';
            $field2Operator = '=';
            $field2Value = $request->input('status');
        }else {
            $field2Name = 'status';
            $field2Operator = '!=';
            $field2Value = "Returned";
        }

        if($request->filled('shift')){
            $field1Name = 'shift';
            $field1Operator = '=';
            $field1Value = $request->input('shift');
        }else {
            $field1Name = 'id';
            $field1Operator = '!=';
            $field1Value = 0;//to return all rows
        }


        if($request->filled('delivery_date')){
            if($request->input('delivery_date') == 'all'){
                $field0Name = 'id';
                $field0Operator = '!=';
                $field0Value = 0; // to return all rows
            }else{
                $field0Name = 'delivery_date';
                $field0Operator = '=';
                $field0Value =  $request->input('delivery_date');
            }
        }elseif($type == "delivery"){
            $field0Name = 'delivery_date';
            $field0Operator = '=';
            $field0Value = Carbon::today()->toDateString();
        }else{
            $field0Name = 'id';
            $field0Operator = '!=';
            $field0Value = 0; // to return all rows
        }

        $rideables = Rideable::with('user','rides','rides.driver','rides.truck','location')
        ->whereHas('location', function($q) use ($operator) {
            $q->where('type', $operator, 'Client');
        })
        ->where([
        ['status','!=','Done'],
        ['status','!=','Canceled'],
        ['status','!=','Return'],
        [$field0Name,$field0Operator,$field0Value],
        [$field1Name,$field1Operator,$field1Value],
        [$field2Name,$field2Operator,$field2Value]
        ])
        ->orderBy($rideableSort, 'asc')
        ->paginate(70);

        ($request!==null) ? $flashId = $request->id : $flashId = '1';

        return view('rideable.rideables',compact('rideables','op1','op2','flashId'));
    }

    public function map(Request $request)
    {
        if(empty($request->input('shift'))){
            $shiftOperator = '!=';
            $shift = 'all';
        }else {
            $shiftOperator = '=';
            $shift = $request->input('shift');
        }

        if(empty($request->input('delivery_date'))){
            $delivery_dateOperator = '=';
            $delivery_date = Carbon::today()->toDateString();
        }elseif($request->input('delivery_date') == 'all' ){
            $delivery_dateOperator = '!=';
            $delivery_date = 'all'; // to return all rows
        }else{
            $delivery_dateOperator = '=';
            $delivery_date = $request->input('delivery_date');
        }

        $queryVars = array('delivery_dateOperator' => $delivery_dateOperator, 'delivery_date' => $delivery_date, 'shiftOperator' => $shiftOperator, 'shift' => $shift );
        $spots = Location::with('rideables')
                            // ->whereDoesntHave('rideables.rides')
                            ->whereHas('rideables', function($q) use($queryVars){
                                $q->where([
                                    // ['type','=','Client'],
                                    ['status', '!=', 'Done'],
                                    ['status', '!=', 'Canceled'],
                                    ['status','!=','Returned'],
                                    ['status','!=','Return'],
                                    ['delivery_date',$queryVars['delivery_dateOperator'],$queryVars['delivery_date']],
                                    ['shift',$queryVars['shiftOperator'],$queryVars['shift']]
                                ]);
                                $q->orWhere([
                                    ['type','=','Warehouse'],
                                    ['status', '!=', 'Done'],
                                    ['status', '!=', 'NotAvailable']
                                ]);
                            })
                            ->get();

        foreach ($spots as $key => $value) {
            Location::addGeo($value);
        }
        $count= $spots->count();
        return view('map',compact('spots','count'));
    }

    public function show(Rideable $rideable)
    {
        return view('rideable.show',compact('rideable'));
    }

    public function status(Request $request)
    {
        $rideable=Rideable::find($request->rideable);
        $rideable->status = $request->status;
        if($rideable->type !='Client'){
            $today = new Carbon();
            $rideable->delivery_date = $today->format('Y-m-d');
            $rideable->shift =  date('H:i');
        }
        Transaction::log(Route::getCurrentRoute()->getName(),Rideable::find($request->rideable),$rideable);
        $rideable->save();

        return redirect()->back()->with('status', $rideable->status.' set');
    }

    // This function is for inserting new invoices into system using excisting multiple line text from other systems.
    public function analyseRaw(Request $request)
    {
        // Sample raw data
        // 432975 │ 05/13/19 │ PAUL'SCOLL │ PAUL'S COLLISSION REPAIR    │         79.00
        // 432976 │ 05/13/19 │ FREEDOMAUT │ FREEDOM AUTO MOTORS(G.P.)   │         80.00
        // 432977 │ 05/13/19 │ IND        │ RENE                        │         27.06
        // 432978 │ 05/13/19 │ BUSY BODY  │ BUSY BODYS AUTO PAINTING    │         42.00

        // rawData will provided to next manual itaraion due to non javascrip blade
        $rawData = $request->rawData;
        // breaking each line to array row
        $rawInvoices=explode("\r\n",$request->rawData);
        $invoices= array();
        foreach ($rawInvoices as $key => $rawInvoice) {
            // each line is going be one row and each row will brake into multiple string(devided by | ), clean from extra white spaces and nested in parents array
            array_push($invoices,(array_map('trim',array_filter(explode(" │ ",$rawInvoice)))));
        }
        $n = 0;

        return view('rideable.batchConfirm', compact('invoices','rawData','n'));
    }

    public function store(Request $request)
    {
        $msg = '';
        for ($i=0,$j=0; $i <= $request->n ; $i++) {
            $thisRequest = $request;
            if ($request->{"invoice_number$i"}!='') {
                $j++;
                $rideable = new Rideable;
                $rideable->user_id = Auth::id();
                if($request->submitType!='batch') {
                    $thisRequest->{"locationName$i"} = $thisRequest->locationName0;
                    $thisRequest->{"locationPhone$i"} = $thisRequest->locationPhone0;
                }
                (is_null($request->{"locationName$i"})) ? $locationName = $thisRequest->{"locationPhone$i"} : $locationName = $thisRequest->{"locationName$i"};
                $location = Location::where('name', $locationName)->first();
                ($thisRequest->type == 'Delivery' && $location == null ) ? redirect()->back()->with('error', 'Location "'.$locationName.'" not exist. Please create it. '):"";
                $rideable->location_id = $location->id;
                $msg .= Location::addGeo($location);
                $rideable->invoice_number = $request->{"invoice_number$i"};
                ($request->{"stock$i"} == 'on') ? $rideable->stock = true :'';
                $rideable->qty = $request->{"qty$i"};
                $rideable->type = Location::find($rideable->location_id)->type;
                $rideable->shift = $request->shift;
                $rideable->delivery_date = $request->delivery_date;
                $rideable->status = 'Created';
                $rideable->description = $thisRequest->description;
                $rideable->save();
                Transaction::log(Route::getCurrentRoute()->getName(),'',$rideable);
            }
        }
        if($request->submitType=='batch') {
            $invoices=null;
            $rawData = $request->rawData;
            $n = 0;
            return view('rideable.batchConfirm', compact('invoices','rawData','n'))->with('status', $j." part number has been added! ".' ');
        }
        else return redirect()->back()->with('status', $j." part number has been added! ".' '.$msg);

    }

    public function batchStore(Request $request)
    {
        for ($i=0,$j=0; $i < 10 ; $i++) {
            $thisRequest = $request;
            $thisRequest->request->add(['invoice_number', $request->{"invoice_number$i"} ]);
            $thisRequest->request->add(['qty', $request->{"qty$i"} ]);
            $thisRequest->request->add(['stock', $request->{"stock$i"} ]);
            if ($thisRequest->invoice_number!=null) {
                $this->store($thisRequest);
                $j++;
            }
        }
    }

    public function batchUpdate(Request $request,$type)
    {
        if($type == "delivery") { $op1 = 'Client';    $op2 = 'Delivery'; $operator = '='; }
        else                    { $op1 = 'Warehouse'; $op2 = 'Pickup';   $operator = '!=';}

        if($request->filled('shift')){
            $field1Name = 'shift';
            $field1Operator = '=';
            $field1Value = $request->input('shift');
        }else {
            $field1Name = 'id';
            $field1Operator = '!=';
            $field1Value = 0; //to return all rows
        }

        if(!$request->filled('delivery_date')){
            $field0Name = 'delivery_date';
            $field0Operator = '=';
            $field0Value = Carbon::today()->toDateString();
        }elseif($request->input('delivery_date') == 'all' && $type == "delivery" ){
            $field0Name = 'id';
            $field0Operator = '!=';
            $field0Value = 0; // to return all rows
        }elseif($request->input('delivery_date') == 'all' && $type != "delivery" ){
            $field0Name = 'delivery_date';
            $field0Operator = '!=';
            $field0Value = 'returned'; // to return all rows
        }else{
            $field0Name = 'delivery_date';
            $field0Operator = '=';
            $field0Value =  $request->input('delivery_date');
        }

        $rideables = Rideable::with('user','rides','rides.driver','rides.truck','location')
            ->whereHas('location', function($q) use ($operator) {
                $q->where('type', $operator, 'Client');
            })
            ->where([
                ['status','!=','Done'],
                ['status','!=','Canceled'],
                ['status','!=','Return'],
                ['status','!=','Returned'],
                [$field0Name,$field0Operator,$field0Value],
                [$field1Name,$field1Operator,$field1Value]
            ]);
            $rideables->update(['delivery_date' =>  $request->input('newDelivery_date')]);
            $rideables = $rideables->update(['shift' =>  $request->input('newShift')]);
            // Transaction::log(Route::getCurrentRoute()->getName(),'',$rideables);

            return redirect()->back()->with('status','Mass update '.$rideables." rides!");
    }

    public function update(Request $request)
    {
        $rideable = Rideable::find($request->id);
        $rideable->invoice_number = $request->invoice_number;
        // $rideable->type = $request->type; //user cant change the type
        ($request->stock == 'on') ? $rideable->stock = true :$rideable->stock = false;
        $rideable->qty = $request->qty;
        $rideable->status = $request->status;
        $rideable->description = $request->description;
        $rideable->shift = $request->shift;
        $rideable->delivery_date = $request->delivery_date;
        if($rideable->rides->count() > 0){
            foreach ($rideable->rides as $ride) {
                $ride->shift = $request->shift;
                $ride->delivery_date = $request->delivery_date;
                $ride->save();
            }
            $msg = 'Ride date/shift updated';
        }
        $rideable->save();
        Transaction::log(Route::getCurrentRoute()->getName(),'',$rideable);

        return redirect()->back()->with('status', '#'.$rideable->invoice_number." updated!");
    }

    public function destroy(Rideable $rideable,Request $request)
    {
            if(Auth::user()->id==$rideable->user_id){
                if($rideable->rides()->count() > 0){
                    $rideable->rides()->detach();
                    $driversName='';
                    foreach (Ride::where('rideable_id',$rideable->id)->get() as $child) {
                        Ride::destroy($child->id);
                        $driversName .= $child->driver->fname.', ';
                    }
                    $msg = 'attached ride destroyed { '.$driversName.'}';
                }else{ $msg = 'no attached ride to destroy!';}
                Rideable::destroy($rideable->id);
                Transaction::log(Route::getCurrentRoute()->getName(),$rideable,false);

                return redirect()->back()->with('status', 'Rideable Destroid! and '.$msg);
            }

        return redirect()->back()->with('status', 'Access Denied. '.$rideable->user->name.' created it and only one who can destroy it!');
    }
}
