<?php

namespace App\Http\Controllers;

use App\Booty;
use App\Snapshot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BootyController extends Controller
{

    public function __construct()
    {
        return $this->middleware('auth:api');
    }


    /**
     * Creates a new virtual machine using the latest code provided
     *
     * @return \Illuminate\Http\Response
     */
    public function createBooty(Request $request)
    {
        $this->validate($request, [
            'source_code' => 'required',
            'app' => 'required'
        ]);

        $booty = Booty::order($request);

        return [
            'status' => 'in-progress',
            'message' => 'New booty ordered',
            'booty' => $booty
        ];   
    }


    /**
     * Orders a new snapshot for the booty
     *
     * @return \Illuminate\Http\Response
     */
    public function createSnapshot( Request $request )
    {
        $this->validate($request, ['booty_id' => 'required']);

        $snapshot = Snapshot::order($request);

        return [
            'status' => 'in-progress',
            'message' => 'Snapshot from booty ordered',
            'snapshot' => $snapshot
        ];
    }


    /**
     * Refresh help prepare a new snapshot with the latest code
     * from the application repository
     *
     * @param Request $request
     * @return void
     */
    public function rebuild (Request $request) 
    {
        $this->validate($request, [
            'source_code' => 'required',
            'app' => 'required'
        ]);

        $snapshot = Snapshot::rebuild($request);

        return [
            'status' => 'in-progress',
            'message' => 'New Snapshot refresh ordered',
            'booty' => $snapshot
        ];   
    }



    /**
     * Deletes the actual VM behind the booty
     *
     * @param Request $request
     * @return void
     */
    public function deleteBooty(Request $request) 
    {
        $this->validate($request, ['id' => 'required']);

        $booty = Booty::find($request->input('id'));

        if ($booty === null) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Can not find the booty'
            ],404);
         }

        $booty->terminate();

        return [
            'status' => 'in-progress',
            'message' => 'Ordered for booty VM deletion',
            'booty' => $booty
        ];  
    }


    /**
     * Deletes the actual cloud image 
     *
     * @param Request $request
     * @return void
     */
    public function deleteSnapshot(Request $request)
    {
        $this->validate($request, ['id' => 'required']);

        $snapshot = Snapshot::find($request->input('id'));

        if ($snapshot === null) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Can not find the snapshot'
            ], 404);
        }

        $snapshot->terminate();

        return [
            'status' => 'in-progress',
            'message' => 'Ordered for snapshot image deletion',
            'snapshot' => $snapshot
        ];  
    }


    public function deleteAll(Request $request)
    {
        $this->validate($request, ['id' => 'required']);
        
        $image = Snapshot::findOrFail($request->input('id'));

        Snapshot::orderImageDelete($image);
        Snapshot::orderSnapshotDelete($image);

        return [
            'status' => 'in-progress',
            'message' => 'Ordered for both image and snapshot deletion',
            'snapshot' => $image
        ];  
    }


    /**
     * Provision a new booty from the latest snapshot of the application
     *
     * @return \Illuminate\Http\Response
     */
    public function provision(Request $request)
    {
        $this->validate($request, [
            'order_id' => 'required',
            'owner_email' => 'required',
            'app' => 'required'
        ]);

        $snapshot = Snapshot::latestFor($request->input('app'));

        if ($snapshot === null) {
            return response()->json( [
                'status' => 'failed',
                'message' => 'Can not find snapshot for the app: ' . $request->input('app')
            ],404);
        }

        $booty = $snapshot->provision( 
            $request->input('order_id'), 
            $request->input('owner_email'),
            $request->input('services')
        );

        return [
            'status' => 'in-progress',
            'message' => 'Provisioning new booty',
            'booty' => $booty
        ];

    }



    /**
     * Assigns a domain name to a Live booty
     *
     * @param String $booty_id
     * @param Request $request
     * @return void
     */
    public function setDomain(String $booty_id, Request $request)
    {
        $this->validate($request, [
            'domain' => 'required|string|min:3'
        ]);

        $booty = Booty::find($booty_id);

        if ($booty === null) {

            return response()->json([
                'status' => 'failed',
                'message' => 'Can not find the domain ID'
            ], 404);

        }

        $provider = empty($request->input('provider')) ? 'DO' : $request->input('provider');
        $domainName = $request->input('domain');
        return Booty::setDomainName($booty, $domainName, $provider);
    }
    
    
}
