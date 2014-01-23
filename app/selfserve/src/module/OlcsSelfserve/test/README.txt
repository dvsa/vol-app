To run all the tests, do:

  phpunit

To run a specific test, do:

  phpunit --filter pattern-to-match-tests-by

If you are on the OLCS development environments standard virtual machine then use the included phpunit by running:

  ../../../vendor/bin/phpunit

When developing tests, using the --strict flag can be useful.
