all: composer-validate cs psalm phpstan phpunit

psalm:
	php vendor/bin/psalm

phpstan:
	php vendor/bin/phpstan analyse

phpunit:
	php vendor/bin/phpunit

cs:
	php vendor/bin/php-cs-fixer fix

composer-validate:
	composer validate
