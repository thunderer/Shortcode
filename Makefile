PHP_VERSION ?= 8.0
PHP := docker-compose run --rm php-${PHP_VERSION}

php-version:
	${PHP} php -v
cache-clear:
	sudo rm -rfv coverage .phpunit.result.cache infection.log

docker-build:
	docker-compose build

composer-install:
	${PHP} composer install
composer-self-update:
	${PHP} composer self-update
composer-update:
	${PHP} composer update
composer-require:
	${PHP} composer require ${PACKAGE}
composer-require-dev:
	${PHP} composer require --dev ${PACKAGE}

test: test-phpunit test-infection qa-psalm
test-phpunit:
	${PHP} php -v
	${PHP} php vendor/bin/phpunit --coverage-text
test-phpunit-local:
	php -v
	php vendor/bin/phpunit --coverage-text
	php vendor/bin/psalm --no-cache
	php vendor/bin/infection
test-infection:
	${PHP} php vendor/bin/infection -j2 --min-msi=80

travis:
	# PHP_VERSION=5.3 make travis-job
	PHP_VERSION=5.4 make travis-job
	PHP_VERSION=5.5 make travis-job
	PHP_VERSION=5.6 make travis-job
	PHP_VERSION=7.0 make travis-job
	PHP_VERSION=7.1 make travis-job
	PHP_VERSION=7.2 make travis-job
	PHP_VERSION=7.3 make travis-job
	PHP_VERSION=7.4 make travis-job
	PHP_VERSION=8.0 make travis-job
travis-job:
	${PHP} composer update --no-plugins
	${PHP} php -v
	${PHP} php vendor/bin/phpunit
	if ${PHP} php -r 'exit((int)(version_compare(PHP_VERSION, "7.1", ">=") === false));'; then \
		${PHP} composer require --dev vimeo/psalm infection/infection; \
		${PHP} vendor/bin/psalm --threads=1 --no-cache --shepherd --find-unused-psalm-suppress; \
		${PHP} vendor/bin/infection; \
		${PHP} composer remove --dev vimeo/psalm infection/infection; \
		fi;

qa-psalm:
	${PHP} php vendor/bin/psalm --no-cache
qa-psalm-suppressed:
	grep -rn psalm-suppress src
