#!/bin/bash

# Proxy to `vendor/bin/composer-update-syms`
# This script kept for to keep people happy who have got used to using it.

if [ -f vendor/bin/composer-update-syms ]; then
  vendor/bin/composer-update-syms
else
  echo "\"olcs/olcs-devtools\" composer dependency needs to be installed. You probably need to run 'composer update' first"
fi