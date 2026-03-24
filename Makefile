DOCKER_COMPOSE_DEV = docker-compose -f docker-compose.yml -f docker-compose.dev.yml
DOCKER_COMPOSE_PROD = docker-compose -f docker-compose.yml -f docker-compose.prod.yml

.PHONY: dev-up dev-down dev-build prod-up prod-down prod-build cache-clear migrations db-diff

dev-up:
	$(DOCKER_COMPOSE_DEV) up -d

dev-down:
	$(DOCKER_COMPOSE_DEV) down

dev-build:
	$(DOCKER_COMPOSE_DEV) build

prod-up:
	$(DOCKER_COMPOSE_PROD) up -d

prod-down:
	$(DOCKER_COMPOSE_PROD) down

prod-build:
	$(DOCKER_COMPOSE_PROD) build

cache-clear:
	$(DOCKER_COMPOSE_DEV) exec app php bin/console cache:clear

migrations:
	$(DOCKER_COMPOSE_DEV) exec app php bin/console doctrine:migrations:migrate --no-interaction

db-diff:
	$(DOCKER_COMPOSE_DEV) exec app php bin/console doctrine:migrations:diff --no-interaction

sh:
	$(DOCKER_COMPOSE_DEV) exec app bash
