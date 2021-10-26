install:
	composer install

validate:
	composer validate

lint:
	composer exec --verbose phpcs -- --standard=PSR12 src bin

lint-fix:
	composer exec --verbose phpcbf -- --standard=PSR12 src bin

temp:
	./bin/gendiff --format json ./fixtures/file1.json ./fixtures/file2.json