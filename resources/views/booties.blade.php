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

                <h3 class="pt-4 pb-3">Booties</h3>

                <table class="table table-dark1 table-striped table-hover table-sm mt-2">
                        <thead>
                        <tr>
                                <th scope="col">#</th>
                                <th scope="col">Order ID</th>
                                <th scope="col">Cusomer Email</th>
                                <th scope="col">Domain / IP</th>
                                <th scope="col">Status</th>
                                <th scope="col">Property</th>
                                <th scope="col">Created</th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach($booties as $b)
                        <tr>
                                <th scope="row">{{ $loop->iteration }}</th>
                                <td style="vertical-align: middle;">
                                {{ substr($b->order_id, 0, 8) }}
                                </td>
                                <td style="vertical-align: middle;">
                                {{ $b->owner_email }}
                                </td>
                                <td style="vertical-align: middle;">
                                @if (empty($b->name))
                                <a target="_blank" href="http://{{ $b->ip }}">{{ $b->ip }}</a>
                                @else
                                <a target="_blank" href="http://{{ $b->name }}">{{ $b->name }}</a>
                                @endif
                                </td>
                                <td style="vertical-align: middle;">
                                @if($b->status === 'Live')
                                <span class="py-1 px-2 rounded bg-success text-white">{{ $b->status }}</span>
                                @else
                                {{ $b->status }}
                                @endif

                                </td>
                                <td style="vertical-align: middle;">
                                <code>{{ $b->region }} / {{ $b->size }} </code>
                                </td>
                                <td style="vertical-align: middle;">{{ $b->created_at->diffForHumans() }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                </table>
            
                <div class="pt-4">
                        {{ $booties->links() }}
                </div>
        </div>
    </div>
</div>
@endsection 