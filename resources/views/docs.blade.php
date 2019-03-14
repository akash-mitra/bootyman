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

            <h3>Overview</h3>
            <p>
                Bootyman helps you to deploy and manage applications or services in self contained virtual machines in cloud environment.
                These machines can be deployed in real-time using the latest codes from source control systems and updated or managed as and when needed using a seamless API interface.
                The API interface makes it easy for other systems to interact with bootyman on-the-fly and provision cloud based systems on demand. 
                Bootyman also provides a simple admin interface for the front-end users to monitor and manage the systems in real-time.
                While Bootyman makes it easy to perform user authentication via traditional login, it also provides Oauth token based APIs to automate machine to machine accesses.
            </p>

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

            <hr>

            <h3>Booty</h3>
            <p>Booty is a virtual machine that is configured with application code</p>


        </div>
    </div>
</div>
@endsection 