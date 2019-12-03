PHP ?= 7.4

composer-update:
	docker-compose run --rm composer composer config platform.php ${PHP}
	docker-compose run --rm composer composer update
	docker-compose run --rm composer composer config --unset platform

test:
	docker-compose run --rm php-${PHP} php -v
	docker-compose run --rm php-${PHP} php /app/vendor/bin/phpunit -c /app/phpunit.xml.dist
test-local:
	php -v
	php vendor/bin/phpunit
