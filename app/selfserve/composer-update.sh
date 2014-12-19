#!/bin/bash

hadSymlink=false

if [ -L "vendor/olcs/OlcsCommon" ]
then
    echo "Removing symlink"
    hadSymlink=true
    rm vendor/olcs/OlcsCommon
fi

composer update

if [ "$hadSymlink" = true ] ;
then
    echo "Recreating symlink"
    rm -rf vendor/olcs/OlcsCommon
    ln -s ../olcs-common vendor/olcs/OlcsCommon
fi