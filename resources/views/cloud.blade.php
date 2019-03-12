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

            <div class="row">
                <h3 class="col-md-8 pt-4 pb-3">Cloud Inventory</h3>
                <h4 class="col-md-4 text-right pt-4 pb-3">Estimated Bill ${{ count($cloud['machines']) * 5 + array_reduce($cloud['images'], function ($s, $i) { $s += $i->sizeGigabytes; return $s; }) * 0.05 }}</h4>
            </div>
            <hr>
            <div class="row">
                <h3 class="col-md-8 pt-4 pb-3">Machines ({{ count($cloud['machines']) }})</h3>
                <h6 class="col-md-4 text-right pt-4 pb-3">Estimated Bill ${{ count($cloud['machines']) * 5 }}</h6>
            </div>
            <table class="table table-dark1 table-striped table-hover table-sm mt-2">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Type</th>
                        <th scope="col">Name</th>
                        <th scope="col">IP</th>
                        <th scope="col">Created</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($cloud['machines'] as $r)
                    <tr>
                        <th scope="row">{{ $loop->iteration }}</th>
                        <th scope="row">Machine</th>
                        <td style="vertical-align: middle;">
                            {{ $r->id }}
                            <span class="text-secondary">{{ $r->name }}</span>
                        </td>
                        <td>{{ $r->networks[0]->ipAddress }}</td>
                        <td>{{ $r->createdAt }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="row">
                <h3 class="col-md-8 pt-4 pb-3">Images ({{ count($cloud['images']) }})</h3>
                <h6 class="col-md-4 text-right pt-4 pb-3">Estimated Bill ${{ array_reduce($cloud['images'], function ($s, $i) { $s += $i->sizeGigabytes; return $s; }) * 0.05 }}</h6>
            </div>
            <table class="table table-dark1 table-striped table-hover table-sm mt-2">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Type</th>
                        <th scope="col">Name</th>
                        <th scope="col">Size (GB)</th>
                        <th scope="col">Created</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($cloud['images'] as $r)
                    <tr>
                        <th scope="row">{{ $loop->iteration }}</th>
                        <th scope="row">Image</th>
                        <td style="vertical-align: middle;">
                            {{ $r->id }}
                            <span class="text-secondary">{{ $r->name }}</span>
                        </td>
                        <td>{{ $r->sizeGigabytes }}</td>
                        <td>{{ $r->createdAt }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>
</div>
@endsection 