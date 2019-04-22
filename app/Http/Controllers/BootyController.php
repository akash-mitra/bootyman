<?php

namespace App\Http\Controllers;

use App\Booty;
use App\Journal;
use App\Snapshot;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BootyController extends Controller
{

    public function __construct(Request $request)
    {
        $this->middleware('auth:api');

        if (!$request->has('order_id')) {
            $request->merge(['order_id' => Journal::unique()]);
        }
    }


    /**
     * Creates a new virtual machine using the latest code provided
     *
     * @return \Illuminate\Http\Response
     */
    public function createBooty(Request $request)
    {
        $this->validate($request, [
            'source_code' => 'required|url',
            'app' => 'required'
        ]);

        Journal::req('Booty: create fresh', 0, __METHOD__, $request->order_id, [
            'request' => $request->input(),
        ]);

        try {
            $booty = Booty::order($request);
            return [
                'status' => 'in-progress',
                'message' => 'New booty ordered',
                'booty' => $booty
            ];
        } catch (Exception $e) {
            return $this->logErrorAndRespond(__METHOD__, 'Failed to create booty.', $e);
        }
    }


    /**
     * Orders a new snapshot for the booty
     *
     * @return \Illuminate\Http\Response
     */
    public function createSnapshot(Request $request)
    {
        $this->validate($request, ['booty_id' => 'required']);

        Journal::req('Snapshot: create from booty', 0, __METHOD__, $request->order_id, [
            'request' => $request->input(),
        ]);

        try {
            $snapshot = Snapshot::order($request);

            return [
                'status' => 'in-progress',
                'message' => 'Snapshot from booty ordered',
                'snapshot' => $snapshot
            ];
        } catch (Exception $e) {
            return $this->logErrorAndRespond(__METHOD__, 'Snapshot creation failed.', $e);
        }
    }


    /**
     * Refresh help prepare a new snapshot with the latest code
     * from the application repository
     *
     * @param Request $request
     * @return void
     */
    public function rebuild(Request $request)
    {
        $this->validate($request, [
            'source_code' => 'required',
            'app' => 'required'
        ]);

        Journal::req('Snapshot: Refresh snapshot with latest code', 0, __METHOD__, $request->order_id, [
            'request' => $request->input(),
        ]);

        try {
            $snapshot = Snapshot::rebuild($request);

            return [
                'status' => 'in-progress',
                'message' => 'New Snapshot refresh ordered',
                'booty' => $snapshot
            ];
        } catch (Exception $e) {
            return $this->logErrorAndRespond(__METHOD__, 'Snapshot refresh failed.', $e);
        }
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

        Journal::req('Booty: Delete request', 0, __METHOD__, $request->order_id, [
            'request' => $request->input(),
        ]);

        try {
            $booty = Booty::findOrFail($request->input('id'));
            $booty->terminate($request);
            return [
                'status' => 'in-progress',
                'message' => 'Ordered for booty VM deletion',
                'booty' => $booty
            ];
        } catch (ModelNotFoundException $e) {
            return $this->logErrorAndRespond(__METHOD__, 'The booty can not be found in bootyman repository.', $e, 404);
        } catch (Exception $e) {
            return $this->logErrorAndRespond(__METHOD__, 'Booty deletion failed.', $e);
        }
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

        Journal::req('Delete a snapshot', 0, __METHOD__, $request->order_id, [
            'request' => $request->input(),
        ]);

        try {
            $snapshot = Snapshot::find($request->input('id'));
            $snapshot->terminate($request);
            return [
                'status' => 'in-progress',
                'message' => 'Ordered for snapshot image deletion',
                'snapshot' => $snapshot
            ];
        } catch (ModelNotFoundException $e) {
            return $this->logErrorAndRespond(__METHOD__, 'The snapshot can not be found in bootyman repository.', $e, 404);
        } catch (Exception $e) {
            return $this->logErrorAndRespond(__METHOD__, 'Snapshot deletion failed.', $e);
        }
    }


    // public function deleteAll(Request $request)
    // {
    //     $this->validate($request, ['id' => 'required']);

    //     $image = Snapshot::findOrFail($request->input('id'));

    //     Snapshot::orderImageDelete($image);
    //     Snapshot::orderSnapshotDelete($image);

    //     return [
    //         'status' => 'in-progress',
    //         'message' => 'Ordered for both image and snapshot deletion',
    //         'snapshot' => $image
    //     ];
    // }


    /**
     * Provision a new booty from the latest snapshot of the application
     *
     * @return \Illuminate\Http\Response
     */
    public function provision(Request $request)
    {
        $this->validate($request, [
            'owner_email' => 'required',
            'app' => 'required'
        ]);

        Journal::req('Booty: Provision from snapshot', 0, __METHOD__, $request->order_id, [
            'request' => $request->input(),
        ]);

        try {
            $snapshot = Snapshot::latestFor($request->input('app'));

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
        } catch (ModelNotFoundException $e) {
            return $this->logErrorAndRespond(__METHOD__, 'No snapshot image exists for the application requested.', $e, 404);
        } catch (Exception $e) {
            return $this->logErrorAndRespond(__METHOD__, 'Booty provision failed', $e);
        }
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



    /**
     * Logs the error and returns a HTTP error response with JSON body
     *
     * @param string $source Source method and/or line number
     * @param string $message Custom error message
     * @param \Exception $e 
     * @param integer $httpStatusCode
     */
    private function logErrorAndRespond(string $source, string $message, Exception $e, $httpStatusCode = 500)
    {
        $request = request();

        Journal::error($e->getMessage(), $e->getCode(), $source, $request->order_id, [
            'request' => $request->input(),
        ]);

        return response()->json([
            'status' => 'failed',
            'message' => $message,
            'order' => request()->order_id
        ], $httpStatusCode);
    }
}
