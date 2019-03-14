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

            <h3 class="pt-4 pb-3">Snapshots</h3>

            <table class="table table-dark1 table-striped table-hover table-sm mt-2">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Name</th>
                        <th scope="col">Application</th>
                        <th scope="col">Booties</th>
                        <th scope="col">Snapshot ID</th>
                        <th scope="col">Status</th>
                        <th scope="col">Created</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($snapshots as $s)
                    <tr>
                        <th scope="row" style=" vertical-align: middle;">
                            <a href="{{ route('snapshots.snapshot', $s->id) }}">{{ str_pad($s->id, 8, '0', STR_PAD_LEFT) }}</a>
                        </th>
                        <td style=" vertical-align: middle;">
                            {{ substr($s->name, 0, 8) }}
                            <span class="text-secondary">{{ substr($s->name, 8, 10) }}</span>
                            {{ substr($s->name, 18) }}
                        </td>
                        <td style=" vertical-align: middle;">
                            <a href="{{ $s->origin->source_code }}">{{ $s->app }}</a>
                        </td>
                        <td style="vertical-align: middle;">
                            <a href="{{ route('snapshots.snapshot', $s->id) }}">{{ count($s->booties) }}</a>
                        </td>
                        <td style="vertical-align: middle;">{{ $s->internal_snapshot_id }}</td>
                        <td style="vertical-align: middle;">
                            @if($s->status === 'Snapshot Ready')
                            <span class="py-1 px-2 rounded bg-success text-white">{{ $s->status }}</span>
                            @else
                            {{ $s->status }}
                            @endif

                        </td>
                        <td style="vertical-align: middle;">{{ $s->created_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="pt-4">
                {{ $snapshots->links() }}
            </div>
        </div>
    </div>
</div>
@endsection 