#!/bin/sh

tar -czf ../release/olcs-static/$VERSION.tar.gz \
public --exclude="public/styleguides"
