Testing
-----
Testing is carried out using Docker and Docker Compose in order to guarantee a consistent testing environment.

The following documentation assumes that you are using a shell that currently has a working directory in the root of
the project.

Build images
=====
```bash
docker-compose build
```

sometimes it makes sense to rebuild them with `--no-cache` to make sure the images are not outdated and still can build fine.

Prepare PHP composer packages
=====
```bash
composer install --prefer-dist
```

Create Containers
======
```bash
docker-compose up
```
This may take a few minutes depending on your internet connection.

Attach to Command Line Interface
======
The following will get you a shell on PHP command line container.
```bash
docker-compose exec phpcli7 /bin/bash
```

Running PHPUnit
======
Simply run PHPUnit

```bash
vendor/bin/phpunit
```

It may be also useful and sometimes required to look at what environment variables travis-ci sets before running PHPUnit to make sure your local test will be close to what travis-ci does. You can do it by inspecting recent job log on travis-ci.com. Just take some recent commit, it should have the link.

To test a particular case use `--filter`:

e.g. if you get:
```
1) Manticoresearch\Test\ClientTest::testGetLastResponse
Manticoresearch\Exceptions\NoMoreNodesException: No more retries left
```

Run this test alone:
```bash
vendor/bin/phpunit -d memory_limit=4G --filter testGetLastResponse test/
```

Generating Local Code Coverage
======
```bash
phpdbg -qrr vendor/bin/phpunit --coverage-html report test
```
A succesful test run will look like this:
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
