<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProviderAddressMapping;
use Yajra\DataTables\DataTables;
use App\Models\User;
use App\Models\Setting;

class ProviderAddressMappingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

    }



    public function index_data(DataTables $datatable,Request $request)
    {

        $providerdata = $request->provideraddress;
        $query = ProviderAddressMapping::query()->myAddress()->where('provider_id',$providerdata);
        $filter = $request->filter;

        if (isset($filter)) {
            if (isset($filter['column_status'])) {
                $query->where('status', $filter['column_status']);
            }
        }
        if (auth()->user()->hasAnyRole(['admin'])) {
            $query->newQuery();
        }

        return $datatable->eloquent($query)
        ->addColumn('check', function ($row) {
            return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-'.$row->id.'"  name="datatable_ids[]" value="'.$row->id.'" onclick="dataTableRowCheck('.$row->id.')">';
        })
        ->editColumn('status' , function ($query){
            $disabled = $query->trashed() ? 'disabled': '';
            return '<div class="custom-control custom-switch custom-switch-text custom-switch-color custom-control-inline">
                <div class="custom-switch-inner">
                    <input type="checkbox" class="custom-control-input  change_status" data-type="provideraddress_status" '.($query->status ? "checked" : "").'  '.$disabled.' value="'.$query->id.'" id="'.$query->id.'" data-id="'.$query->id.'">
                    <label class="custom-control-label" for="'.$query->id.'" data-on-label="" data-off-label=""></label>
                </div>
            </div>';
        })

        ->editColumn('provider_id', function ($query) {
            return view('provideraddress.user', compact('query'));
        })

        ->filterColumn('provider_id',function($query,$keyword){
            $query->whereHas('providers',function ($q) use($keyword){
                $q->where('first_name','like','%'.$keyword.'%');
            });
        })

        ->addColumn('action', function($provideraddress){
            return view('provideraddress.action',compact('provideraddress'))->render();
        })
        ->addIndexColumn()
        ->rawColumns(['check','provider_id', 'action','status'])
        ->toJson();
    }

    /* bulck action method */
    public function bulk_action(Request $request)
    {
        $ids = explode(',', $request->rowIds);

        $actionType = $request->action_type;

        $message = 'Bulk Action Updated';
        switch ($actionType) {
            case 'change-status':
                $branches = ProviderAddressMapping::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = 'Bulk Provider Address Status Updated';
                break;

            case 'delete':
                ProviderAddressMapping::whereIn('id', $ids)->delete();
                $message = 'Bulk Provider Address Deleted';
                break;

            default:
                return response()->json(['status' => false, 'message' => 'Action Invalid']);
                break;
        }

        return response()->json(['status' => true, 'message' => $message]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $id = $request->id;
        $auth_user = authSession();
        $provider_address = $request->provideraddress;
        $providerdata = User::with('handymanAddressMapping')->where('user_type','provider')->where('id',$provider_address)->first();
        $provideraddress = ProviderAddressMapping::find($id);
        $pageTitle = trans('messages.update_form_title',['form'=>trans('messages.provider_address')]);

        if($provideraddress == null){
            $pageTitle = trans('messages.add_button_form',['form' => trans('messages.provider_address')]);
            $provideraddress = new ProviderAddressMapping;
        }

        return view('provideraddress.create', compact('pageTitle' ,'provideraddress' ,'auth_user','providerdata' ));
    }

    public function getLatLong(Request $request)
    {
        $address = $request->input('address');

        // Retrieve the Google Maps API key from your settings
        $sitesetup = Setting::where('type', 'site-setup')->where('key', 'site-setup')->first();
        $sitesetupdata = json_decode($sitesetup->value);
        $googleMapKey = $sitesetupdata->google_map_keys;

        config(['geocoder.providers.Geocoder\Provider\Chain\Chain.Geocoder\Provider\GoogleMaps\GoogleMaps' => [
            env('GOOGLE_MAPS_LOCALE', 'us'),
            $googleMapKey,
        ]]);

        $result =  app('geocoder')->geocode($address)->get();
        $lat = null;
        $long = null;
        if ($result->isNotEmpty()) {
            $coordinates = $result->first()->getCoordinates();
            $lat = $coordinates->getLatitude();
            $long = $coordinates->getLongitude();
        }
        return response()->json(['latitude' => $lat, 'longitude' => $long]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(demoUserPermission()){
            return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }
        $data = $request->all();
        if (auth()->user()->hasRole('provider')) {
            $data['provider_id'] = auth()->id();
        }
        $data['provider_id'] = !empty($data['provider_id']) ? $data['provider_id'] : auth()->user()->id;
        $result = ProviderAddressMapping::updateOrCreate(['id' => $data['id'] ],$data);

        $message = __('messages.update_form',['form' => __('messages.provider_address')]);
        if($result->wasRecentlyCreated){
            $message = __('messages.save_form',['form' => __('messages.provider_address')]);
        }

        if($request->is('api/*')) {
            return comman_message_response($message);
		}

        return redirect(route('provideraddress.show',['provideraddress' => $result->provider_id]))->withSuccess($message);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,$id)
    {
        $auth_user = authSession();
        $providerdata = User::with('handymanAddressMapping')->where('user_type','provider')->where('id',$id)->first();
        $filter = [
            'status' => $request->status,
        ];
        $pageTitle = trans('messages.list_form_title',['form' => trans('messages.provider_address')] );
        $assets = ['datatable'];
        if(empty($providerdata))
        {
            $msg = __('messages.not_found_entry',['name' => __('messages.provider')] );
            return redirect(route('provider.index'))->withError($msg);
        }
        return view('provideraddress.view', compact('pageTitle','auth_user','assets','filter','providerdata'));

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(demoUserPermission()){
            if(request()->is('api/*')){
                return comman_message_response( __('messages.demo_permission_denied') );
            }
            return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }
        $provideraddress = ProviderAddressMapping::find($id);
        $msg = __('messages.msg_fail_to_delete',['item' => __('messages.provider_address')] );

        if( $provideraddress!='') {

            $provideraddress->delete();
            $msg= __('messages.msg_deleted',['name' => __('messages.provider_address')] );
        }
        if(request()->is('api/*')){
            return comman_custom_response(['message'=> $msg , 'status' => true]);
        }
        return comman_custom_response(['message'=> $msg, 'status' => true]);
    }

    public function action(Request $request){
        $id = $request->id;

        $provideraddress  = ProviderAddressMapping::withTrashed()->where('id',$id)->first();
        $msg = __('messages.not_found_entry',['name' => __('messages.provider_address')] );
        if($request->type == 'restore') {
            $provideraddress->restore();
            $msg = __('messages.msg_restored',['name' => __('messages.provider_address')] );
        }
        if($request->type === 'forcedelete'){
            $provideraddress->forceDelete();
            $msg = __('messages.msg_forcedelete',['name' => __('messages.provider_address')] );
        }
        return comman_custom_response(['message'=> $msg , 'status' => true]);
    }
}
