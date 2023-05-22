# Testing

We use GitHub Actions as a CI tool, but you can also run testing manually with the help of Docker Compose to run Manticore Search in containers, and PHPUnit, PHPStan, and PHPCS for tests.

The following documentation assumes that you are using a shell with the working directory set to the root of the project.

## Build images
```bash
docker-compose build
```

Sometimes it's a good idea to rebuild the images with `--no-cache` to ensure they are not outdated and can still be built correctly.

## Prepare PHP composer packages
```bash
composer install --prefer-dist
```

## Create Containers
```bash
docker-compose up
```
This process might take a few minutes, depending on your internet connection.

## Attach to Command Line Interface
The following command will give you access to the PHP command line container shell:
```bash
docker-compose exec phpcli7 /bin/bash
```

## Running PHPUnit
To run PHPUnit, simply execute the following command:

```bash
vendor/bin/phpunit
```

To test a particular case, use the `--filter`option:

For example, if you get:
```
1) Manticoresearch\Test\ClientTest::testGetLastResponse
Manticoresearch\Exceptions\NoMoreNodesException: No more retries left
```

Run this specific test with:
```bash
vendor/bin/phpunit -d memory_limit=4G --filter testGetLastResponse test/
```

## Generating Local Code Coverage
```bash
phpdbg -qrr vendor/bin/phpunit --coverage-html report test
```
A successful test run should appear like this:
```bash
root@807c5bdfe018:/var/www# phpdbg -qrr vendor/bin/phpunit --coverage-html report test
PHPUnit 7.5.20 by Sebastian Bergmann and contributors.

...............................................................  63 / 166 ( 37%)
................S...S..........S............................... 126 / 166 ( 75%)
..................................S.....                        166 / 166 (100%)

Time: 29.28 seconds, Memory: 16.00 MB

OK, but incomplete, skipped, or risky tests!
Tests: 166, Assertions: 260, Skipped: 4.

Generating code coverage report in HTML format ... done

```
<!-- proofread -->