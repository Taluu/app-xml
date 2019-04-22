ifndef APP_ENV
	include .env
endif

build: ## Build the application
build: vendors
	docker-compose exec php bin/console cache:clear --no-interaction
	docker-compose exec php bin/console cache:warmup --no-interaction
	docker-compose exec php bin/console doctrine:database:drop --force --no-interaction --if-exists
	docker-compose exec php bin/console doctrine:database:create --no-interaction
	docker-compose exec php bin/console doctrine:schema:create --no-interaction
	docker-compose exec php bin/console doctrine:fixtures:load --no-interaction
	docker-compose exec php bin/console doctrine:migrations:migrate --no-interaction
	docker-compose up -d web
.PHONY: build

vendors: ## Install vendors
vendors:
	docker-compose up -d php
	docker-compose exec php composer install
.PHONY: vendors

tests: ## Run behat and linter tests
tests: linter
.PHONY: tests

linter: ## Run static analyzer linter tests
linter: vendors
	docker-compose up -d php
	docker-compose exec php phpstan analyze -c phpstan.neon src fixtures --level=max
.PHONY: linter

help: ## Get help on that makefile
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
.PHONY: help
