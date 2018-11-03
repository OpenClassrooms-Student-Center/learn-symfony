DOCKER          = docker
DOCKER_COMPOSE  = docker-compose
PHP_SERVICE     = $(DOCKER_COMPOSE) exec php sh -c
CONSOLE         = $(DOCKER_COMPOSE) exec php bin/console

##
## ----------------------------------------------------------------------------
##   Environment
## ----------------------------------------------------------------------------
##

build: ## Build the environment
	$(DOCKER_COMPOSE) build

install: ## Install the environment
	make build start composer yarn
	@make reset APP_ENV=dev
	@echo "Accédez au blog de Zozor! http://localhost:8000"

logs: ## Follow logs generated by all containers
	$(DOCKER_COMPOSE) logs -f --tail=0

logs-full: ## Follow logs generated by all containers from the containers creation
	$(DOCKER_COMPOSE) logs -f

ps: ## List all containers managed by the environment
	$(DOCKER_COMPOSE) ps

restart: ## Restart the environment
	$(DOCKER_COMPOSE) restart

start: ## Start the environment
	$(DOCKER_COMPOSE) up -d --remove-orphans

stats: ## Print real-time statistics about containers ressources usage
	$(DOCKER) stats $($(DOCKER) ps --format={{.Names}})

stop: ## Stop the environment
	$(DOCKER_COMPOSE) stop

uninstall: ## Uninstall the environment
	make config
	$(DOCKER_COMPOSE) kill
	$(DOCKER_COMPOSE) down --volumes --remove-orphans

.PHONY: build config install logs logs-full ps restart start stats stop uninstall

##
## ----------------------------------------------------------------------------
##   Project
## ----------------------------------------------------------------------------
##

console: ## Access Symfony Console
	$(CONSOLE) $(filter-out $@,$(MAKECMDGOALS))
composer: ## Install Composer dependencies from the "php" container
	$(PHP_SERVICE) "composer install --optimize-autoloader"

encore-dev: ## Compile assets once with Encore/Webpack
	$(PHP_SERVICE) "yarn run encore dev"

encore-prod: ## Compile assets once with Encore/Webpack and minify & optimize them
	$(PHP_SERVICE) "yarn run encore production"

encore-watch: ## Compile assets automatically with Encore/Webpack when files change
	$(PHP_SERVICE) "yarn run encore dev --watch"

nginx: ## Open a terminal in the "nginx" container
	$(DOCKER_COMPOSE) exec nginx sh

php: ## Open a terminal in the "php" container
	$(DOCKER_COMPOSE) exec php sh

reset: ## Reset the database used by the specified environment
	$(PHP_SERVICE) "export APP_ENV=${APP_ENV} && \
		php bin/console doctrine:database:drop --force && \
		php bin/console doctrine:database:create && \
		php bin/console doctrine:schema:create --no-interaction && \
		php bin/console doctrine:fixtures:load --no-interaction"

yarn: ## Install Yarn dependencies from the "php" container"
	$(PHP_SERVICE) "yarn install"

.PHONY: console composer encore-dev encore-prod encore-watch nginx php reset yarn

##
## ----------------------------------------------------------------------------
##   Quality
## ----------------------------------------------------------------------------
##

check: ## Execute all quality assurance tools
	make lint phpcsfixer phpunit security

lint: ## Lint YAML configuration, Twig templates and JavaScript files
	$(PHP_SERVICE) "php bin/console lint:yaml config"
	$(PHP_SERVICE) "php bin/console lint:twig templates"

phpcsfixer: ## Run the PHP coding standards fixer on dry-run mode
	@test -f .php_cs || cp .php_cs.dist .php_cs
	$(PHP_SERVICE) "php vendor/bin/php-cs-fixer fix --config=.php_cs \
		--cache-file=var/cache/.php_cs --verbose --dry-run"

phpcsfixer-apply: ## Run the PHP coding standards fixer on apply mode
	@test -f .php_cs || cp .php_cs.dist .php_cs && \
	$(PHP_SERVICE) "php vendor/bin/php-cs-fixer fix --config=.php_cs \
		--cache-file=var/cache/.php_cs --verbose"

phpstan: ## Run the PHP static analysis tool at level 4
	$(PHP_SERVICE) "php ./vendor/bin/phpstan analyse -c phpstan.neon -l 4 src"

phpunit: ## Run the tests suit (unit & functional)
	$(PHP_SERVICE) "php ./vendor/bin/simple-phpunit"

security: ## Run a security analysis on dependencies
	$(PHP_SERVICE) "php bin/console security:check"

.PHONY: check lint phpcsfixer phpcsfixer-apply phpunit security phpstan

.DEFAULT_GOAL := help
help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' \
		| sed -e 's/\[32m##/[33m/'
.PHONY: help
