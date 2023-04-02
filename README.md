# Guestbook Unicorn API
A public API for our unicorn farm.
- Symfony 6.2
- PHP 8.1
- Docker compose

## Setup

Clone the project from the [master repository](https://github.com/JQHNNY/unicorn-api)
### Using Docker
````
git clone https://github.com/JQHNNY/unicorn-api.git
cd unicorn-api
````
### Install dependencies
````
make install
````

### Build and start Docker containers

````
make start
````

### Run migrations
````
make migrate
````

### Start local webserver and open local webmail
````
make server

# http://localhost:8000/ - Local webserver
# http://localhost:1080/ - Local webmail
````
Running this command might not open your local webmail via your browser. The URL will be listed in your terminal when running this command.
[Mailcatcher should be exposed on port 1080](https://symfony.com/doc/current/setup/symfony_server.html#docker-integration), you can always check via Docker Desktop.



## Using the API

After setting up the project head over to the documentation page by visiting
### You can use Postman to test the API or try out the calls via the documentation
````
http://domain:port/api/doc 
Example : http://localhost:8000/api/doc 

#Your browser might give you a Potential Security Risk Ahead warning because our local web server doesn't support https.
#You can proceed by clicking on the accept the risk button.
````

