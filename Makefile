.PHONY: up test cache-clear composer

up:
	docker compose up -d

test:
	docker exec -e APP_ENV=test currency_rates_application vendor/bin/phpunit

cache-clear:
	docker exec -e APP_ENV=test currency_rates_application php bin/console cache:clear

composer:
	docker exec currency_rates_application composer $(filter-out $@,$(MAKECMDGOALS))

update-rates:
	docker exec currency_rates_application php bin/console currency:update-rates
