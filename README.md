# Guestbook Unicorn API
A public API for our unicorn farm.
- Symfony 6.2
- PHP 8.1
- Docker compose

## Setup

Clone the project from the [master repository](https://github.com/JQHNNY/unicorn-api)
### Using Docker
````
git clone git@github.com:JQHNNY/unicorn-api.git
cd unicorn
````
### Install dependencies
````
make install
````

### Build and start Docker containers

````
make start
````

### Build database
````
make start_db
````

### Start local webserver and open local webmail
````
make server
````
Your local webmail might not open on your browser. The URL will be listed in your terminal when running this command.
