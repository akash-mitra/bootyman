<?php

namespace App\Http\Controllers;

use App\Snapshot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SnapshotController extends Controller
{

    public function __construct()
    {
        return $this->middleware('auth:api');
    }


    /**
     * Creates a new image using latest code
     *
     * @return \Illuminate\Http\Response
     */
    public function createImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'source_code' => 'required',
            'commit_id' => 'required'
        ]);
        if ($validator->fails()) {
            return $validator->messages();
        }

        $snapshot = Snapshot::orderImage(
            $request->input('source_code'),
            $request->input('commit_id'),
            empty($request->input('branch')) ? 'master' : $request->input('branch'),
            empty($request->input('type')) ? 'ubuntu-18-04-x64' : $request->input('type'),
            empty($request->input('provider')) ? 'DO' : $request->input('provider')
        );

        return [
            'status' => 'in-progress',
            'message' => 'New image ordered',
            'snapshot' => $snapshot
        ];   
    }


    /**
     * Orders a new snapshot building process from the image supplied
     *
     * @return \Illuminate\Http\Response
     */
    public function createSnapshot( Request $request )
    {
        $validator = Validator::make($request->all(), ['imageId' => 'required']);
        if ($validator->fails()) {
            return $validator->messages();
        }

        $imageId = $request->input('imageId');
        $snapshot = Snapshot::orderSnapshot($imageId, 'DO');

        return [
            'status' => 'in-progress',
            'message' => 'Snapshot from image ordered',
            'snapshot' => $snapshot
        ];
    }


    public function refresh (Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'source_code' => 'required',
            'commit_id' => 'required'
        ]);
        if ($validator->fails()) {
            return $validator->messages();
        }

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
     * Returns the snapshot with most latest code
     *
     * @return \Illuminate\Http\Response
     */
    public function latest()
    {
        
        return Snapshot::latestReady();
    }


    public function deleteImage(Request $request) 
    {
        $validator = Validator::make($request->all(), ['id' => 'required']);
        if ($validator->fails()) {
            return $validator->messages();
        }

        $image = Snapshot::findOrFail($request->input('id'));

        Snapshot::orderImageDelete($image);

        return [
            'status' => 'in-progress',
            'message' => 'Ordered for image deletion',
            'snapshot' => $image
        ];  
    }


    public function deleteSnapshot(Request $request)
    {
        $validator = Validator::make($request->all(), ['id' => 'required']);
        if ($validator->fails()) {
            return $validator->messages();
        }

        $image = Snapshot::findOrFail($request->input('id'));

        Snapshot::orderSnapshotDelete($image);

        return [
            'status' => 'in-progress',
            'message' => 'Ordered for snapshot deletion',
            'snapshot' => $image
        ];  
    }


    public function deleteAll(Request $request)
    {
        $validator = Validator::make($request->all(), ['id' => 'required']);
        if ($validator->fails()) {
            return $validator->messages();
        }

        $image = Snapshot::findOrFail($request->input('id'));

        Snapshot::orderImageDelete($image);
        Snapshot::orderSnapshotDelete($image);

        return [
            'status' => 'in-progress',
            'message' => 'Ordered for both image and snapshot deletion',
            'snapshot' => $image
        ];  
    }
    
    
}
