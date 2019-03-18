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

Next run

```
php artisan passport:install
php artisan queue:restart
```

### Getting Started

Unless you modify ADMIN_USER and ADMIN_PASSWORD variables in `.env` file before executing `php artisan migrate --seed`, the application will install with a default user (admin@example.com) and password (secret) in the beginning. Do not forget to change the password after logging in for the first time. Generate a new Token for other applications to be able to connect to Bootyman and place API requests.

### Quick API Reference

#### Create a new Booty
To create a new VM pre-configured with a Laravel application from Github, use this. In below example, we are creating 
``` json
curl --request POST \
  --url >>>Your_bootyman_application_url<<< \
  --header 'authorization: Bearer >>>your_token_here<<<' \
  --header 'content-type: application/json' \
  --data '{
	"source_code": "https://github.com/your-name/application.git",
	"app": "your-application"
}'
```



