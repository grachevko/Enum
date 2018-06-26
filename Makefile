all: cs phpstan phpunit

phpstan:
	php vendor/bin/phpstan analyse --level 6 src tests

phpunit:
	php vendor/bin/phpunit

cs:
	php vendor/bin/php-cs-fixer fix
