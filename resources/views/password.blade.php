@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            @if (session('status.success'))
            <div class="alert alert-success" role="alert">
                {{ session('status.success') }}
            </div>
            @endif

            @if (session('status.failure'))
            <div class="alert alert-danger" role="alert">
                {{ session('status.failure') }}
            </div>
            @endif

            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <h3>Change your password</h3>
            <p>&nbsp;</p>
            <form method="post" action="{{ route('password.change') }}">
                @csrf
                <input type="hidden" name="email" value="{{ auth()->user()->email }}">
                <div class="form-group">
                    <label for="currentPass">Current Password</label>
                    <input type="password" name="current_password" class="form-control" id="currentPass" placeholder="Current Password">
                </div>

                <div class="form-group">
                    <label for="newPass">New Password</label>
                    <input type="password" name="password" class="form-control" id="newPass" placeholder="New Password">
                    <small>Password must be 8 characters long. At least 1 numeric, 1 alpha, 1 Uppercase, 1 Lowercase and 1 special character required.</small>
                </div>

                <div class="form-group">
                    <label for="retypeNewPass">Re-type New Password</label>
                    <input type="password" name="password_confirmation" class="form-control" id="retypeNewPass" placeholder="New Password">
                </div>

                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
</div>
@endsection 