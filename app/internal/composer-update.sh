#!/bin/bash

hadCommonSymlink=false
hadTransferSymlink=false

if [ -L "vendor/olcs/OlcsCommon" ]
then
    echo "Removing symlink"
    hadCommonSymlink=true
    rm vendor/olcs/OlcsCommon
fi

if [ -L "vendor/olcs/olcs-transfer" ]
then
    echo "Removing symlink"
    hadTransferSymlink=true
    rm vendor/olcs/olcs-transfer
fi

if [ -f composer.phar ] ;
then
    php composer.phar update
else
    composer update
fi

if [ "$hadCommonSymlink" = true ] ;
then
    echo "Recreating symlink"
    rm -rf vendor/olcs/OlcsCommon
    cd vendor/olcs && ln -s ../../../olcs-common/ OlcsCommon
fi

if [ "$hadTransferSymlink" = true ] ;
then
    echo "Recreating symlink"
    rm -rf vendor/olcs/olcs-transfer
    cd vendor/olcs && ln -s ../../../olcs-transfer/ olcs-transfer
fi
