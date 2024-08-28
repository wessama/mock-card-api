setup:
	docker-compose up -d --build
	cp .env .env.local
	docker-compose exec worker composer install
	docker-compose exec worker php bin/console doctrine:fixtures:load --no-interaction
	docker-compose exec worker php bin/console lexik:jwt:generate-keypair --skip-if-exists

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