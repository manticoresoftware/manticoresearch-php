Testing
-----
Testing is carried out using Docker and Docker Compose in order to guarantee a consistent testing environment.

The following documentation assumes that you are using a shell that currently has a working directory in the root of
the project.

Create Containers
======
```bash
sudo docker-compose exec phpcli /bin/bash
```
This may take a few minutes depending on your internet connection.

Attach to Command Line Interface
======
The following will get you a shell on PHP command line container.
```bash
sudo docker-compose exec phpcli /bin/bash
```

Running PHPUnit
======
Simply run PHPUnit

```bash
vendor/bin/phpunit
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
