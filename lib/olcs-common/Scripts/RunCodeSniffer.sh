#!/bin/sh

for repo in $(ls -1 | grep olcs); do
	cd "$repo"

	phpcs -p --standard="${dev_workspace}/sonar-configuration/Profiles/DVSA/CS/ruleset.xml" --extensions=php --ignore=vendor/ .

	cd ../
done

