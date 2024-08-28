setup:
	$(MAKE) build
	cp .env .env.local
	$(MAKE) install
	$(MAKE) migrate
	$(MAKE) jwt
	$(MAKE) fixtures
	$(MAKE) test
	$(MAKE) dev-server

down:
	docker-compose down

build:
	docker-compose up -d --build

install:
	docker-compose exec worker composer install

migrate:
	docker-compose exec worker php bin/console doctrine:migrations:migrate --no-interaction

fixtures:
	docker-compose exec worker php bin/console doctrine:fixtures:load --no-interaction

jwt:
	docker-compose exec worker php bin/console lexik:jwt:generate-keypair --skip-if-exists

test:
	docker-compose exec worker php bin/phpunit

dev-server:
	docker-compose exec worker php -S 0.0.0.0:8000 -t public