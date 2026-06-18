#!/bin/bash

BASE_BRANCH=${1-"origin/develop"}

# this parses the 'project name' from the git remote url
PROJECT=$(git remote -v | head -n1 | awk '{print $2}' | sed 's/.*\///' | sed 's/\.git//');

echo "{panel:title=$PROJECT|borderStyle=solid|borderColor=#000|titleBGColor=#75e069|bgColor=#efefef}"

echo "h2.Check PHP syntax"

echo "{code}"

for file in $(git diff $BASE_BRANCH --name-only);
do
	if [ -f $file ]
		then
		if [[ ${file: -4} == ".php" ]]
			then
			php -l $file;
		fi
	fi
done

echo "{code}"

echo "h2.Check Coding Standards"

echo "{code}"

for file in $(git diff $BASE_BRANCH --name-only);
do
	if [ -f $file ]
		then
		if [[ ${file: -4} == ".php" ]]
			then
			./vendor/bin/phpcs --standard="${dev_workspace}/sonar-configuration/Profiles/DVSA/CS/ruleset.xml" $file;
		fi
	fi
done

echo "{code}"

echo "h2.Run unit tests"

echo "{code}"

cd test && ../vendor/bin/phpunit --coverage-php `pwd`/review/coverage.cov

echo "{code}"

echo "h2.Checking coverage of diff"

echo "{code}"

cd .. && git diff $BASE_BRANCH > test/review/patch.txt && vendor/phpunit/phpcov/phpcov patch-coverage --patch test/review/patch.txt --path-prefix `pwd`/ test/review/coverage.cov

echo "{code}"

echo "{panel}"
#git diff $BASE_BRANCH