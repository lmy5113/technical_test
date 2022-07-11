-include .env.docker.local

DATABASE_NAME:=gsoi_test
DOCKER_USER:=gsoi
DOCKER_COMPOSE:=docker-compose
DOCKER_COMPOSE_RUN:=$(DOCKER_COMPOSE) run --user=gsoi --rm --no-deps
DOCKER_COMPOSE_EXEC:=$(DOCKER_COMPOSE) exec --user=gsoi
DOCKER_SYMFONY_CONSOLE:=$(DOCKER_COMPOSE_RUN) php php bin/console
CHANGED_FILES:=$(shell git diff --name-only)
USERID:=$(shell id -u)
GROUPID:=$(shell id -g)

help: ## Show this help.
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//' | grep -v '###'

-wait-db:
	$(shell sleep 5)

build: ## Build containers
	eval "$(ssh-agent)"
	DOCKER_BUILDKIT=1 $(DOCKER_COMPOSE) build --build-arg USERID=$(USERID) --build-arg GROUPID=$(GROUPID)

clean: ## Clean everything
	$(DOCKER_COMPOSE) down -v --remove-orphans
	rm -rf ./vendor
	rm -rf ./var/cache/*
	rm -rf ./var/log/*
	rm -rf ./.ecs_cache

install: build install-vendor start -wait-db migrations-run ## install project

install-vendor: ## Install composer dependencies
	$(DOCKER_COMPOSE_RUN) php composer install --prefer-dist -vv

reload-database: ## Reload database
	$(DOCKER_SYMFONY_CONSOLE) doctrine:database:drop --force --if-exists --env=dev
	$(DOCKER_SYMFONY_CONSOLE) doctrine:database:create --if-not-exists --env=dev
	$(MAKE) migrations-run

migrations-run: ## Run migrations
	$(DOCKER_SYMFONY_CONSOLE) doc:migration:migrate --all-or-nothing -n --allow-no-migration

migrations-create: ## Create a migration with current database diff
	$(DOCKER_SYMFONY_CONSOLE) make:migration -n

ssh: ## Log into php container
	$(DOCKER_COMPOSE_EXEC) php ash

start: ## Start containers
	$(DOCKER_COMPOSE) up -d
	@echo "Api accessible here http://127.0.0.1"

stop: ## Stop containers
	$(DOCKER_COMPOSE) stop

restart: ## Restart containers
	$(DOCKER_COMPOSE) restart

%:
    @:

# TOOLS

analyze-code: ## Run code analyzers
	$(DOCKER_COMPOSE_RUN) php php -d memory_limit=4096M ./vendor/bin/ecs --config=./ecs.php check
	$(DOCKER_COMPOSE_RUN) php php -d memory_limit=4096M ./vendor/bin/phpstan analyze -c ./phpstan.neon
	$(DOCKER_COMPOSE_RUN) php php -d memory_limit=4096M ./vendor/bin/psalm -c ./psalm.xml

fix-code: ## Apply code fixer
	$(DOCKER_COMPOSE_RUN) php php -d memory_limit=4096M ./vendor/bin/ecs --config=./ecs.php --fix

prepare-test-database: ## Prepare test database
	$(DOCKER_COMPOSE_RUN) php php bin/console doctrine:database:drop --force --if-exists --env=test
	$(DOCKER_COMPOSE_RUN) php php bin/console doctrine:database:create --if-not-exists --env=test
	$(DOCKER_COMPOSE_RUN) php php bin/console doctrine:schema:update --force --env=test

test: analyze-code prepare-test-database test-unit test-functional ## Launch tests

test-unit: ## Launch unit tests
#	$(DOCKER_COMPOSE_RUN) php php ./vendor/bin/phpunit --testsuite Unit

test-functional: ## Launch functional tests
	$(DOCKER_COMPOSE_RUN) php php ./vendor/bin/phpunit --testsuite Functional -vvv
