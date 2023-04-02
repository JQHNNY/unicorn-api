# Executables (local)
DOCKER_COMP = docker compose

# Docker containers
PHP_CONT = $(DOCKER_COMP) exec php

# Executables
SYMFONY  = $(EXEC_PHP) bin/console
BINARY = $(EXEC_PHP) symfony
COMPOSER = composer

# Misc
.DEFAULT_GOAL = help
.PHONY        : help build up start down logs sh composer vendor sf cc

## Commands
install: composer.lock ## Install vendors according to the current composer.lock file
	@$(COMPOSER) install --no-progress --prefer-dist --optimize-autoloader

build: ## Builds the Docker images
	@$(DOCKER_COMP) build --pull --no-cache

up: ## Start the docker hub
	@$(DOCKER_COMP) up -d

start: build up ## Build and start the containers, start the local webserver

down: ## Stop the docker hub
	@$(DOCKER_COMP) stop

migrate: ## Build database
	@$(SYMFONY) doctrine:migrations:migrate -n

rebuild_db: ## Build database
	@$(SYMFONY) doctrine:database:drop --force
	@$(SYMFONY) doctrine:database:create --if-not-exists
	@$(SYMFONY) doctrine:migrations:migrate -n

server:
	@$(BINARY) server:start -d
	@$(BINARY) open:local:webmail

server_down:
	@$(BINARY) server:stop
