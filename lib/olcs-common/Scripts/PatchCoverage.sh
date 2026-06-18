#!/bin/bash

cd test && ../vendor/bin/phpunit --coverage-php `pwd`/review/coverage.cov

cd .. && git diff origin/develop > test/review/patch.txt && vendor/phpunit/phpcov/phpcov patch-coverage --patch test/review/patch.txt --path-prefix `pwd`/ test/review/coverage.cov