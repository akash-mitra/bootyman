### Overview
        
Bootyman helps you to deploy and manage applications or services in self contained virtual machines in cloud environment.
These machines can be deployed in real-time using the latest codes from source control systems and updated or managed as and when needed using a seamless API interface.

The API interface makes it easy for other systems to interact with bootyman on-the-fly and provision cloud based systems on demand. 
Bootyman also provides a simple admin interface for the front-end users to monitor and manage the systems in real-time.
While Bootyman makes it easy to perform user authentication via traditional login, it also provides Oauth token based APIs to automate machine to machine accesses.
        

#### Typical Usecase

To understand how Bootyman can be helpful, consider you have created a Node or Laravel application that you intend to deploy
in a self-contained container or virtual machine for each of your individual users. You further want to ensure that virtual machines are always deployed with latest code and machine configurations happen in real time. In such scenarios, Bootyman can help you as follows


- Your CI/CD systems can directly interact with Bootyman via Bootyman API. This way, whenever you push a new feature update, bootyman can keep your application images updated in the cloud.
- You can integrate Bootyman with your User onboarding system, so that any time a new user is created, a new virtual machine is configured for the user with latest code
- You can use Bootyman to update softwares in provisioned systems, or to manage accesses or security keys or even to run scripts in such systems
- You can monitor and action any provisioning errors, security incidents etc. from the web front-end
            

### Booty

Booty is a virtual machine that is installed and pre-configured with your application code. You can create as many booties as you want to serve your users in the cloud environment. They can be created or disposed very easily or can be stored as an OS image snapshot for future use. 

### Snapshot

Images of your booties at any point in time can be stored as snapshots. At later point of time, a snapshot can be used to regenerate the image quickly. Bootyman provides API to create snapshot from the images and to restore images from snapshots. Whenever your application code is modified, bootyman creates booty with latest code and then make snapshot of the booty so that the same can be reinstated quickly whenever needed. 

### Installation

This is a complete Laravel application. Download it in your local or remote machine and then run composer install.

```
mkdir bootyman
cd bootyman 
git clone https://github.com/akash-mitra/bootyman.git
composer install 
php artisan key:generate
cp .env.example .env
```

Update `.env` file database connections, provide a new email for ADMIN_USER variable and then run `php artisan migrate --seed`

Next run,

```
php artisan passport:install
php artisan queue:restart
```

### Getting Started

Unless you modify ADMIN_USER and ADMIN_PASSWORD variables in `.env` file before executing `php artisan migrate --seed`, the application will install with a default user (`admin@example.com`) and password (`secret`) in the beginning. Do not forget to change the password after logging in for the first time. After logging in, generate a new API Token for other applications to be able to connect to Bootyman and place API requests.

### Quick API Reference

#### Create a new Booty
To create a new VM pre-configured with a Laravel application from Github, use this. In below example, we are creating a new booty with your latest application code. 

``` json
curl --request POST \
  --url [Your_bootyman_application_url] \
  --header 'authorization: Bearer [your_token_here]' \
  --header 'content-type: application/json' \
  --data '{
	"source_code": "https://github.com/your-name/your-application.git",
	"app": "your-application"
}'
```

After a booty is generated, you may wish to create a snapshot of this booty so that next time you can create multiple booties using the mother snapshot.

#### Create a snapshot from existing booty

To create a new snapshot, you need to provide the ID of the booty that you need to generate the snapshot from,

``` json
curl --request POST \
  --url [Your_bootyman_application_url] \
  --header 'authorization: Bearer [your_token_here]' \
  --header 'content-type: application/json' \
  --data '{
	"booty_id": [specify_booty_id]
}'
```

Once you have a snapshot ready, feel free to create as many booties as and when required using this mother snapshot.

#### Provision a new machine from existing booty

To provision a new booty, use the below API call,

``` json
curl --request POST \
  --url [Your_bootyman_application_url] \
  --header 'authorization: Bearer [your_token_here]' \
  --header 'content-type: application/json' \
  --data '{
	"order_id": [an_unique_number_pertaining_to_this_request],
	"owner_email": [email_id_of_the_requester],
	"app": "bootyman",
	"services": {
		"laravel-passport": true,
		"commands": [
			"touch /var/www/app/bootyman/testfile",
		]
	}
}'
```

As you can see above, the request can take a `order_id` and `owner_email` to help you identify the owner and the requestor. This is often helpful in cases where you call this API automatically in response to some user's request to create this VM. You are also required to provide a name to your application in the request via `app` parameter. This helps you maintain multiple applications in bootyman environment.

##### Services

In the above request, a `services` parameter is also passed. This parameter is optional. However, this parameter can contain names of services or commands that need to be started or executed respectively in the newly created VM. Below is a list of services that are currently supported.

1. `laravel-queue` - This will start the laravel queue service along with `supervisorctl`
2. `laravel-passport` - This will start laravel passport service.

##### Commands

Under the `commands` array, you can provide number of `shell` commands that you wish to execute once the booty in provisioned (these are passed via `cloudinit` parameter). These commands are executed with `appuser` privilege.

