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

Booty is a virtual machine that is configured with application code


