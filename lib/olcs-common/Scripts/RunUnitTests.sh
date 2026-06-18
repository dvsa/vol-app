#!/bin/sh

for repo in $(ls -1 | grep olcs); do
	if [ -d "$repo/test" ]; then
		cd "$repo/test"
		phpunit
		cd ../../
	fi
done
