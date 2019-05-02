<?php

namespace App\Http\Controllers;

use App\Booty;
use App\Snapshot;
use App\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use App\CloudProviders\DigitalOceanService;
use Yajra\DataTables\Facades\DataTables;

class HomeController extends Controller
{

    public function welcome()
    {
        return view('welcome');
    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $booties = Booty::where('status', 'Live')->count();
        $snapshots = Snapshot::where('status', '!=', 'Deleted')->count();
        $tokens = \DB::table('oauth_access_tokens')->where('revoked', 0)->count();
        $events = Journal::orderBy('id', 'desc')->take(25)->get();
        // $events = DataTables::eloquent(Journal::query())->make(true);
        return view('home')
            ->with('booties', $booties)
            ->with('snapshots', $snapshots)
            ->with('tokens', $tokens)
            ->with('events', $events)
            ->with('jobs', Queue::size());
    }


    public function anyData()
    {
        return DataTables::of(Journal::query())
            ->rawColumns(['context']) // do not want JSON to be escaped in the view
            ->make(true);
    }

    public function snapshots()
    {
        $snapshots = Snapshot::latest()->paginate(25);
        return view('snapshots', compact('snapshots'));
    }

    public function snapshot($snapshot_id)
    {
        $snapshot = Snapshot::findOrfail($snapshot_id);
        return view('snapshot', compact('snapshot'));
    }

    public function booties()
    {
        $booties = Booty::latest()->paginate(25);
        return view('booties', compact('booties'));
    }

    public function cloud()
    {
        $cloud = DigitalOceanService::resources();
        return view('cloud', compact('cloud'));
    }

    public function token()
    {
        return view('token');
    }


    public function errors()
    {
        return 'Up-coming feature';
    }


    public function docs()
    {
        return view('docs');
    }


    public function passwordShow()
    {
        return view('password');
    }

    public function bootiesSoftDelete(Booty $booty)
    {
        $booty->delete();

        session()->flash('status', sprintf("Booty [%s] deleted successfully.", $booty->id));

        return redirect()->back();
    }


    public function snapshotsSoftDelete(Snapshot $snapshot)
    {
        $snapshot->delete();

        session()->flash('status', sprintf("Snapshot [%s] deleted successfully.", $snapshot->id));

        return redirect()->back();
    }


    public function passwordChange(Request $request)
    {
        $this->validate($request, [
            '_token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/',
            'current_password' => 'required'
        ]);

        $credentials = $request->only('email', 'password', 'password_confirmation', 'current_password');
        $user = \Auth::user();

        // make sure supplied current password is correct password of the user
        if (Hash::check($credentials['current_password'], $user->password)) {
            // change the password
            $user->password = Hash::make($credentials['password']);
            $user->save();
            $request->session()->flash('status.success', 'Password changed successfully! Please re-login.');
        } else {
            $request->session()->flash('status.failure', 'Wrong password supplied for the current user');
        }
        return redirect()->back();
    }
}
