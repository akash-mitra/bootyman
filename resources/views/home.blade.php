@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
            @endif

            

            <div class="card-deck mb-3 text-center">
                <div class="card mb-4 box-shadow">
                    <div class="card-header">
                        <h4 class="my-0 font-weight-normal">VM</h4>
                    </div>
                    <div class="card-body">
                        <h1 class="card-title pricing-card-title">{{ $booties }} <small class="text-muted">Booties</small></h1>
                        <p>Total deployed VMs</p>

                    </div>
                </div>
                <div class="card mb-4 box-shadow">
                    <div class="card-header">
                        <h4 class="my-0 font-weight-normal">Images</h4>
                    </div>
                    <div class="card-body">
                        <h1 class="card-title pricing-card-title">{{ $snapshots }} <small class="text-muted">Snapshots</small></h1>
                        <p>Total image snapshots available on cloud</p>

                    </div>
                </div>
                <div class="card mb-4 box-shadow">
                    <div class="card-header">
                        <h4 class="my-0 font-weight-normal">Errors</h4>
                    </div>
                    <div class="card-body">
                        <h1 class="card-title pricing-card-title">0 <small class="text-muted">errors</small></h1>
                        <p>No. of uncleared errors</p>

                    </div>
                </div>
                <div class="card mb-4 box-shadow">
                    <div class="card-header">
                        <h4 class="my-0 font-weight-normal">Tokens</h4>
                    </div>
                    <div class="card-body">
                        <h1 class="card-title pricing-card-title">{{ $tokens}} <small class="text-muted">tokens</small></h1>
                        <p>Passport access tokens issued</p>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection 