@extends('layouts.app')

@section('css')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
<style>
    pre {
        padding: 5px;
        white-space: pre-wrap;
    }

    .string {
        color: green;
    }

    .number {
        color: darkorange;
    }

    .boolean {
        color: blue;
    }

    .null {
        color: magenta;
    }

    .key {
        color: black;
        font-weight: bold
    }

    table.dataTable tbody tr.shown {
        color: indigo
    }

    table.dataTable tbody tr.shown+tr>td {
        border-top: none
    }

    .info {
        background-color: cornsilk;
        color: darkslateblue;
    }

    .request {
        background-color: aliceblue;
        color: green;
    }

    .warning {
        background-color: blanchedalmond;
        color: crimson;
    }

    .error {
        background-color: red;
        color: white;
    }
</style>
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
                <table class="table table-responsive table-sm text-sm display compact" id="events-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Type</th>
                            <th>Origin</th>
                            <th>Message</th>
                            <th>Timestamp</th>
                            <th>Action</th>
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
    function syntaxHighlight(json) {

        if (typeof json != 'string') {
            json = JSON.stringify(json, undefined, 2);
        }
        json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function(match) {
            var cls = 'number';
            if (/^"/.test(match)) {
                if (/:$/.test(match)) {
                    cls = 'key';
                } else {
                    cls = 'string';
                }
            } else if (/true|false/.test(match)) {
                cls = 'boolean';
            } else if (/null/.test(match)) {
                cls = 'null';
            }
            return '<span class="' + cls + '">' + match + '</span>';
        });
    }


    // this returns the HTML required to display the context information
    function format(d) {
        return '<pre>' + syntaxHighlight(JSON.stringify(JSON.parse(d.context), undefined, '\t')) + '</pre>';
    }


    $(function() {

        // dataTable invocation
        let table = $('#events-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{!! route("events.datatables.data") !!}',
            columns: [{
                    data: 'order_id',
                    name: 'order_id'
                },
                {
                    data: 'type',
                    name: 'type'
                },
                {
                    data: 'origin',
                    name: 'origin'
                },
                {
                    data: 'message',
                    name: 'message'
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    "className": 'details-control',
                    "orderable": false,
                    "defaultContent": '<a href="#">Check</a>'
                },
            ],
            order: [
                [4, "desc"],
                [0, 'desc']
            ],
            rowCallback: function(row, data, index) {
                // for changing the background color of the "type" column
                $(row).find('td:eq(1)').addClass(data.type)
            },
            pageLength: 25,
            deferRender: true
        });

        // Add event listener for opening and closing details
        $('#events-table tbody').on('click', 'td.details-control', function() {
            
            var tr = $(this).closest('tr');
            var row = table.row(tr);

            if (row.child.isShown()) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
            } else {
                // Open this row
                row.child(format(row.data())).show();
                tr.addClass('shown');
            }
        });
    });
</script>
@endsection