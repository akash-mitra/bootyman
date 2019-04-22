<?php

namespace App\Http\Controllers;

use App\Booty;
use App\Snapshot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\CloudProviders\DigitalOceanService;

class HomeController extends Controller
{

    public function welcome () 
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
        $snapshots = Snapshot::count();
        $tokens = \DB::table( 'oauth_access_tokens')->where('revoked', 0)->count();
        return view('home')
        ->with('booties', $booties)
        ->with('snapshots', $snapshots)
        ->with('tokens', $tokens);
    }

    public function snapshots()
    {
        $snapshots = Snapshot::latest()->paginate(25);
        return view( 'snapshots', compact('snapshots'));
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
        if (Hash::check( $credentials['current_password'], $user->password)) 
        {
            // change the password
            $user->password = Hash::make( $credentials['password'] );
            $user->save();
            $request->session()->flash('status.success', 'Password changed successfully! Please re-login.');
        }
        else {
            $request->session()->flash('status.failure', 'Wrong password supplied for the current user');
            
        }
        return redirect()->back();
    }
}
