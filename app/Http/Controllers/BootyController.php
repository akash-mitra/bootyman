<?php

namespace App\Http\Controllers;

use App\Booty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BootyController extends Controller
{
    public function __construct()
    {
        return $this->middleware('auth:api');
    }



    /**
     * Provision a new booty for the customer
     *
     * @return \Illuminate\Http\Response
     */
    public function provision(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'owner_email' => 'required'
        ]);
        if ($validator->fails()) {
            return $validator->messages();
        }

        return Booty::provision (
            $request->input('order_id'),
            $request->input('owner_email'),
            empty($request->input('name')) ? '' : $request->input('name'),
            empty($request->input('size')) ? 's-1vcpu-1gb' : $request->input('size'),
            empty($request->input('region')) ? 'sgp-1' : $request->input('region'),
            empty($request->input('provider')) ? 'DO' : $request->input('provider')
        );
    }

}
