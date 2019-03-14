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
        
        $booty = Booty::order(
            empty($request->input('provider')) ? env('DEFAULT_INFRA_PROVIDER', 'DO') : $request->input('provider'),
            empty($request->input('region')) ? env('DEFAULT_INFRA_REGION', 'sgp1') : $request->input('region'),
            empty($request->input('size')) ? env('DEFAULT_INFRA_SIZE', 's-1vcpu-1gb') : $request->input('size'),
            empty($request->input('type')) ? env('DEFAULT_INFRA_OS_TYPE', 'ubuntu-18-04-x64') : $request->input('type'),
            $request->input('app'),
            $request->input('source_code'),
            empty($request->input('branch')) ? 'master' : $request->input('branch'),
            empty($request->input('commit_id')) ? 'latest' : $request->input('commit_id'),
            empty($request->input('order_id')) ? 0 : $request->input('order_id'),
            empty($request->input('orderer')) ? auth()->user()->email : $request->input('orderer'),
            empty($request->input('sshkey')) ? env('DEFAULT_INFRA_SSH_KEY', '60344') : $request->input('sshkey')
        );

        return [
            'status' => 'in-progress',
            'message' => 'New booty ordered',
            'snapshot' => $booty
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
        
        $booty_id = $request->input('booty_id');
        $snapshot = Snapshot::order(
            $booty_id,
            empty($request->input('provider')) ? env('DEFAULT_INFRA_PROVIDER', 'DO') : $request->input('provider'),
            empty($request->input('order_id')) ? 0 : $request->input('order_id'),
            empty($request->input('orderer')) ? auth()->user()->email : $request->input('orderer')
        );

        return [
            'status' => 'in-progress',
            'message' => 'Snapshot from booty ordered',
            'snapshot' => $snapshot
        ];
    }


    public function refresh (Request $request) 
    {
        $this->validate($request, [
            'source_code' => 'required',
            'commit_id' => 'required'
        ]);
        

        $snapshot = Snapshot::orderRefresh(
            $request->input('source_code'),
            $request->input('commit_id'),
            empty($request->input('branch')) ? 'master' : $request->input('branch'),
            empty($request->input('type')) ? 'ubuntu-18-04-x64' : $request->input('type'),
            empty($request->input('provider')) ? 'DO' : $request->input('provider')
        );

        return [
            'status' => 'in-progress',
            'message' => 'New Snapshot refresh ordered',
            'snapshot' => $snapshot
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

        $booty = $snapshot->provision( $request->input('order_id'), $request->input('owner_email'));

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
