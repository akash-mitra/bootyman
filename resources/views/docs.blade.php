@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
            @endif

            <h3>Booty Manager Overview</h3>
            <p>
                Bootyman helps you to deploy and manage applications or services in self contained virtual machines in cloud environment.
                These machines can be deployed in real-time using the latest codes from source control systems and updated or managed as and when needed using a seamless API interface.
                The API interface makes it easy for other systems to interact with bootyman on-the-fly and provision cloud based systems on demand.
                Bootyman also provides a simple admin interface for the front-end users to monitor and manage the systems in real-time.
                While Bootyman makes it easy to perform user authentication via traditional login, it also provides Oauth token based APIs to automate machine to machine accesses.
            </p>
            <p>&nbsp;</p>
            <h4>Typical Usecase</h4>
            <p>
                To understand how Bootyman can be helpful, consider you have created a Node or Laravel application that you intend to deploy
                in a self-contained container or virtual machine for each of your individual users. You further want to ensure that virtual machines are
                always deployed with latest code and machine configurations happen in real time. In such scenarios, Bootyman can help you as follows

            </p>
            <ul>
                <li>Your CI/CD systems can directly interact with Bootyman via Bootyman API. This way, whenever you push a new feature update, bootyman can keep your application images updated in the cloud.</li>
                <li>You can integrate Bootyman with your User onboarding system, so that any time a new user is created, a new virtual machine is configured for the user with latest code</li>
                <li>You can use Bootyman to update softwares in provisioned systems, or to manage accesses or security keys or even to run scripts in such systems</li>
                <li>You can monitor and action any provisioning errors, security incidents etc. from the web front-end
            </ul>

            <p>&nbsp;</p>
            <hr>
            <p>&nbsp;</p>
            <h4>Booty</h4>
            <p>
                Booty is a virtual machine that is installed and pre-configured with your application code.
                You can create as many booties as you want to serve your users in the cloud environment.
                They can be created or disposed very easily or can be stored as an OS image snapshot for future use.
            </p>

            <h4>Snapshot</h4>
            <p>
                Images of your booties at any point in time can be stored as snapshots. At later point of time, a snapshot can be used
                to regenerate the image quickly. Bootyman provides API to create snapshot from the images and to restore images from
                snapshots. Whenever your application code is modified, bootyman creates booty with latest code and then make snapshot of the
                booty so that the same can be reinstated quickly whenever needed.
            </p>

            <p>&nbsp;</p>
            <h3>API Reference</h3>
            <p>
                Supported APIs are listed below.
            </p>

            <p>&nbsp;</p>

            <h4>1. Create a booty</h4>
            <p>Creates and configures a new booty with application code</p>
            <table class="table">

                <tr>
                    <td><strong>Endpoint</strong></td>
                    <td><code>/create/booty</code></td>
                </tr>
                <tr>
                    <td><strong>Request Type</strong></td>
                    <td><code>POST</code></td>
                </tr>
                <tr>
                    <td><strong>Parameters</strong></td>
                    <td>
                        <table class="table-sm table-borderless">
                            <tr>
                                <td>
                                    <pre>source_code</pre>
                                </td>
                                <td>URL to the application source code</td>
                            </tr>
                            <tr>
                                <td>
                                    <pre>app</pre>
                                </td>
                                <td>Name of the application</td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td><strong>Response</strong></td>
                    <td>
                        <table class="table-sm table-borderless">
                            <tr>
                                <td>
                                    <pre>status</pre>
                                </td>
                                <td>Status of this request, "in-progress" or "failed"</td>
                            </tr>
                            <tr>
                                <td>
                                    <pre>message</pre>
                                </td>
                                <td>Human friendly message to accompany with the status</td>
                            </tr>
                            <tr>
                                <td>
                                    <pre>booty.order_id</pre>
                                </td>
                                <td>Order Id of the request. If no order_id is supplied, this will be 0</td>
                            </tr>
                            <tr>
                                <td>
                                    <pre>booty.owner_email</pre>
                                </td>
                                <td>Orderer email ID of the request. If no email id is supplied, this will be populated with the email id of the user who owns the auth token</td>
                            </tr>
                            <tr>
                                <td>
                                    <pre>booty.status</pre>
                                </td>
                                <td>Status of the booty</td>
                            </tr>
                            <tr>
                                <td>
                                    <pre>booty.order_id</pre>
                                </td>
                                <td>Order Id of the request. If no order_id is supplied, this will be 0</td>
                            </tr>
                            <tr>
                                <td>
                                    <pre>booty.provider</pre>
                                </td>
                                <td>Provider of the cloud infrastructure. E.g. "DO" for DigitalOcean</td>
                            </tr>
                            <tr>
                                <td>
                                    <pre>booty.size</pre>
                                </td>
                                <td>Size of the booty</td>
                            </tr>
                            <tr>
                                <td>
                                    <pre>booty.region</pre>
                                </td>
                                <td>Cloud Region where the booty is being created</td>
                            </tr>
                            <tr>
                                <td>
                                    <pre>booty.type</pre>
                                </td>
                                <td>OS type of the booty</td>
                            </tr>
                            <tr>
                                <td>
                                    <pre>booty.backup</pre>
                                </td>
                                <td>True if automatic cloud level backup is enabled</td>
                            </tr>
                            <tr>
                                <td>
                                    <pre>booty.monitoring</pre>
                                </td>
                                <td>True if cloud monitoring is enabled</td>
                            </tr>
                            <tr>
                                <td>
                                    <pre>booty.sshkey</pre>
                                </td>
                                <td>SSH Key fingerprint. This is the primary access key for the booty</td>
                            </tr>
                            <tr>
                                <td>
                                    <pre>booty.app</pre>
                                </td>
                                <td>Name of the application to be installed and configured in the booty</td>
                            </tr>
                            <tr>
                                <td>
                                    <pre>booty.source_code</pre>
                                </td>
                                <td>URL pointing to the source code repository of the application</td>
                            </tr>
                            <tr>
                                <td>
                                    <pre>booty.branch</pre>
                                </td>
                                <td>Name of the branch of the source code repository</td>
                            </tr>
                            <tr>
                                <td>
                                    <pre>booty.commit</pre>
                                </td>
                                <td>Latest commit id of the source code repo</td>
                            </tr>
                            <tr>
                                <td>
                                    <pre>booty.updated_at</pre>
                                </td>
                                <td>Last update datetime of the booty </td>
                            </tr>
                            <tr>
                                <td>
                                    <pre>booty.created_at</pre>
                                </td>
                                <td> Creation datetime of the booty</td>
                            </tr>
                            <tr>
                                <td>
                                    <pre>booty.id</pre>
                                </td>
                                <td>Unique ID of the Booty. The booty can be referred using this ID in future</td>
                            </tr>
                        </table>
                    </td>
                </tr>

            </table>
            <!-- <h4>Curl Request Example</h4>
            <pre>curl --request POST \
  --url https://bootyman.app/api/create/booty \
  --header 'authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjM3MDFiNTMxZTQ0ZTZkNDdkMDEzZjljMWQ2M2M1MjNhNGEwMWY4NjM2YjQzNTJmNzE2ZGQ2YmEyZWU3N2Y2N2JmYmYyYzJiOGRlMjcwNTkxIn0.eyJhdWQiOiIxIiwianRpIjoiMzcwMWI1MzFlNDRlNmQ0N2QwMTNmOWMxZDYzYzUyM2E0YTAxZjg2MzZiNDM1MmY3MTZkZDZiYTJlZTc3ZjY3YmZiZjJjMmI4ZGUyNzA1OTEiLCJpYXQiOjE1NTIxNDkyMjEsIm5iZiI6MTU1MjE0OTIyMSwiZXhwIjoxNTgzNzcxNjIxLCJzdWIiOiIxIiwic2NvcGVzIjpbXX0.Gda9tkhsZ69MnvZEckHkNNnidTb14SWbEsX-aOC7w0_9cAhXYVJyJLyK0ln0IhHaePO8xopTjit3L8EAsCfz1eZnKuU3lEG-2SpbFEOmqeqAVawfrOwprMKy9ITftId0SyOtLeXGxL1XF44XRtf3xykYRW7jUeYKWX8ATUwKiOrqiWFFVNtZOcZ_arHGTon3fN9L61y-sY-hN6hfqM6QTg4Wmx8ubjiqfNxpl2H4255h51HB4_YF4_y896ZiGieJ6Z8I2zeCJhrPrE6kEnApM6V0mf2dUb5GivFbn4FMWFELeyMjcnx1_HDXRQggR0xqHGX_OK0qZUW8oeb5MdHomLtVy70PfclVAJ1EBhwwtE0d-OpG7_Mb3Qe1F0lINCrpNHyey4KANsFhGbaIcBey5RHdXoM_O4a_W_fNnpSxEZFth1fVhxteR3gYLVfJavm05qfBo4_eOQTIth7MXOBil7JhM7UgTawQTWNlA6Hx84WSXlpu_dg_Rd8lMPBGNe3jAo2m9AuXcHS5am_-d259sMS5RR1HMuSUys8JrmcgbfH_OTdPBmhnA4thqbwP1Fi9isgrlSw2DqnuuxxFhrRaVyCnZsS77VaDFHwLcQeg2mmIfQlrX9GY8FoUSKywF4uoas3Mfb5kZeM7zbjoCD1lVF23tQrkts2_K1XRFVt9Rds' \
  --header 'content-type: application/json' \
  --data '{
	"source_code": "https://github.com/akash-mitra/bootyman.git",
	"app": "bootyman"
}'</pre>

            <h4>Response Example</h4>
            <pre>{
  "status": "in-progress",
  "message": "New image ordered",
  "snapshot": {
    "order_id": 0,
    "owner_email": "akashmitra@gmail.com",
    "status": "Initiated",
    "provider": "DO",
    "size": "s-1vcpu-1gb",
    "region": "sgp1",
    "type": "ubuntu-18-04-x64",
    "backup": false,
    "monitoring": false,
    "sshkey": "60344",
    "app": "bootyman",
    "source_code": "https:\/\/github.com\/akash-mitra\/bootyman.git",
    "branch": "master",
    "commit": "latest",
    "updated_at": "2019-03-14 08:52:22",
    "created_at": "2019-03-14 08:52:22",
    "id": 10
  }
}</pre> -->

        </div>
    </div>
</div>
@endsection   