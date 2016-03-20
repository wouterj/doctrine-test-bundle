init_composer:
	curl -s https://getcomposer.org/installer | php
	php composer.phar install --prefer-dist -o

test:
	vendor/bin/phpunit -c tests/ tests/

build: init_composer test

