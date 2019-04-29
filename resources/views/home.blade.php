@extends('layouts.app')

@section('css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
@endsection

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
                        <p>Deployed VMs</p>

                    </div>
                </div>
                <div class="card mb-4 box-shadow">
                    <div class="card-header">
                        <h4 class="my-0 font-weight-normal">Images</h4>
                    </div>
                    <div class="card-body">
                        <h1 class="card-title pricing-card-title">{{ $snapshots }} <small class="text-muted">Snapshots</small></h1>
                        <p>Image snapshots taken</p>

                    </div>
                </div>
                <div class="card mb-4 box-shadow">
                    <div class="card-header">
                        <h4 class="my-0 font-weight-normal">Job Queue</h4>
                    </div>
                    <div class="card-body">
                        <h1 class="card-title pricing-card-title">{{ $jobs }} <small class="text-muted">Jobs</small></h1>
                        <p>Current Workload</p>

                    </div>
                </div>
                <div class="card mb-4 box-shadow">
                    <div class="card-header">
                        <h4 class="my-0 font-weight-normal">Tokens</h4>
                    </div>
                    <div class="card-body">
                        <h1 class="card-title pricing-card-title">{{ $tokens}} <small class="text-muted">tokens</small></h1>
                        <p>Access tokens issued</p>

                    </div>
                </div>
            </div>

            <h3>Events</h3>
            <p>Events capture the requests, errors and information related to each incoming order to the system</p>
            <div class="">
                <table class="bg-white table table-sm table-bordered table-striped1" id="events-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Type</th>
                            <th>Origin</th>
                            <th>Message</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>

                </table>

                
            </div>
        </div>
    </div>
</div>
@endsection


@section('script')
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>

<script>
    $(function() {
        $('#events-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{!! route("events.datatables.data") !!}',
            columns: [
                {data: 'order_id', name: 'order_id'},
                {data: 'type', name: 'type'},
                {data: 'origin', name: 'origin'},
                {data: 'message', name: 'message'},
                {data: 'created_at', name: 'created_at'},
            ],
            order: [[ 4, "desc" ]]
        });
    });
</script>
@endsection