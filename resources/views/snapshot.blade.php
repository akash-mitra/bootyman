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

            <h4>
                <a href="/snapshots" class="block text-secondary h6 mb-4">Snapshots > </a>
            </h4>
            <h3 class="py-2">
                <div>
                    {{ substr($snapshot->name, 0, 8) }}<span class="text-secondary">{{ substr($snapshot->name, 8, 4) }}</span>{{ substr($snapshot->name, 12) }}
                    @if($snapshot->status === 'Snapshot Ready')
                    <span class="text-right pull-right border border-success px-2 rounded text-success">{{ $snapshot->status }}</span>
                    @else
                    <span class="text-right pull-right border px-2 rounded">{{ $snapshot->status }}</span>
                    @endif
                </div>
            </h3>

            <table>
                <tbody>
                    <tr>
                        <td class="px-1"><span class="text-secondary">Repository:</span></td>
                        <td class="px-4"><code>{{ $snapshot->origin->source_code }}</code></td>
                    </tr>
                    <tr>
                        <td class="px-1"><span class="text-secondary">Branch:</span></td>
                        <td class="px-4"><code>{{ $snapshot->origin->branch }}</code></td>
                    </tr>
                    <tr>
                        <td class="px-1"><span class="text-secondary">Commit:</span></td>
                        <td class="px-4"><code>{{ $snapshot->origin->commit }}</code></td>
                    </tr>
                    <tr>
                        <td class="px-1"><span class="text-secondary">Order:</span></td>
                        <td class="px-4"><code>Order ID: {{ $snapshot->order_id }} From {{ $snapshot->owner_email }}</code></td>
                    </tr>
                    <tr>
                        <td class="px-1"><span class="text-secondary">Last Updated:</span></td>
                        <td class="px-4"><code>{{ $snapshot->updated_at->format('l jS \\of F Y h:i:s A') }}</code></td>
                    </tr>
                </tbody>
            </table>

            <h3 class="mt-4">Booties</h3>
            <table class="table table-dark1 table-striped table-hover table-sm mt-4">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Order ID</th>
                        <th scope="col">Cusomer Email</th>
                        <th scope="col">Domain / IP</th>
                        <th scope="col">Status</th>
                        <th scope="col">Created</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($snapshot->booties as $b)
                    <tr>
                        <th scope="row">{{ $b->id }}</th>
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

                        <td style="vertical-align: middle;">{{ $b->created_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>
</div>
@endsection 