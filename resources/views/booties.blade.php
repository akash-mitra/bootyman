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
                        <th scope="col">App Repo</th>
                        <th scope="col">Domain / IP</th>
                        <th scope="col">Status</th>
                        <th scope="col">Property</th>
                        <th scope="col">Origin</th>
                        <th scope="col">Updated</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($booties as $b)
                    <tr>
                        <th scope="row">{{ $loop->iteration }}</th>
                        <td style="vertical-align: middle;">
                            @if(empty($b->order_id))
                            Internal
                            @else
                            {{ $b->order_id }}
                            @endif

                        </td>
                        <td style="vertical-align: middle;">
                            <a href="{{$b->source_code}}">{{ substr(explode("/", $b->source_code)[count(explode("/", $b->source_code)) - 1], 0, -4) }}</a>
                        </td>
                        <td style="vertical-align: middle;">
                            @if (empty($b->name) && ! empty($b->ip))
                            <a target="_blank" href="http://{{ $b->ip }}">{{ $b->ip }}</a>
                            @elseif($b->name === 'Updating...')
                            <span class="text-primary">Update in progress</span>
                            @elseif($b->name === 'Error!')
                            <span class="text-danger">Failed</span>
                            @else
                            <a target="_blank" title="{{ $b->ip }}" href="http://{{ $b->name }}">{{ $b->name }}</a>
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
                        <td style="vertical-align: middle;">
                            @if(empty($b->snapshot_id))
                            Fresh
                            @else
                            {{$b->snapshot_id}}
                            @endif

                        </td>
                        <td style="vertical-align: middle;">{{ $b->updated_at->diffForHumans() }}</td>
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