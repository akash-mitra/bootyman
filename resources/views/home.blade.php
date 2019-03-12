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


            <h3>1. Get Latest Snapshot Info</h3>
            <p>To get the info about the latest snapshot, use the <code>/snapshots/latest</code> endpoint.</p>
            <h4>Request</h4>
            <pre>
    curl --request GET \
    --url https://bootyman.app/api/snapshots/latest \
    --header 'authorization: Bearer xxxxyyyyzzzz'
</pre>
            <h4>Response</h4>
            <pre>
{
  "id": 20,
  "name": "20190311D135908414N20",
  "provider": "DO",
  "resource_id": "0",
  "internal_snapshot_id": "44578145",
  "source_code": "https:\/\/github.com\/akash-mitra\/blogtheory.git",
  "branch": "master",
  "commit_id": "1377af6",
  "type": "ubuntu-18-04-x64",
  "status": "Snapshot Ready",
  "env": "local",
  "created_at": "2019-03-11 11:36:08",
  "updated_at": "2019-03-11 11:56:16"
}
</pre>

        </div>
    </div>
</div>
@endsection 