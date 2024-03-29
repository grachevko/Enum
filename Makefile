VOLUMES = -v `pwd`:/data -w /data

php = docker run --rm $(OPTIONS) $(VOLUMES) php:8.0-alpine
composer = docker run --rm $(VOLUMES) composer:2.1.5

all: install composer-validate php-cs-fixer psalm phpstan phpunit

install:
	$(composer) install

psalm:
	$(php) vendor/bin/psalm

phpstan:
	$(php) vendor/bin/phpstan analyse

phpunit:
	$(php) vendor/bin/phpunit

php-cs-fixer:
	$(php) vendor/bin/php-cs-fixer fix

composer-validate:
	$(composer) validate

cli: OPTIONS=-ti
cli:
	$(php) sh
