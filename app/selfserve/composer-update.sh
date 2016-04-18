#!/bin/bash

hadCommonSymlink=false
hadTransferSymlink=false
hadUtilsSymlink=false
hadAuthSymlink=false

# Common
if [ -L "vendor/olcs/OlcsCommon" ]
then
    echo "Removing symlink"
    hadCommonSymlink=true
    rm vendor/olcs/OlcsCommon
fi


# Transfer
if [ -L "vendor/olcs/olcs-transfer" ]
then
    echo "Removing symlink"
    hadTransferSymlink=true
    rm vendor/olcs/olcs-transfer
fi


# Utils
if [ -L "vendor/olcs/olcs-utils" ]
then
    echo "Removing symlink"
    hadUtilsSymlink=true
    rm vendor/olcs/olcs-utils
fi


# Auth
if [ -L "vendor/olcs/olcs-auth" ]
then
    echo "Removing symlink"
    hadAuthSymlink=true
    rm vendor/olcs/olcs-auth
fi






if [ -f composer.phar ] ;
then
    php composer.phar update
else
    composer update
fi

if [ "$hadCommonSymlink" = true ] || [ "$1" = "--force" ];
then
    if [ -d "vendor/olcs/OlcsCommon" ];
    then
        echo "Recreating symlink"
        rm -rf vendor/olcs/OlcsCommon
        (cd vendor/olcs && ln -s ../../../olcs-common/ OlcsCommon)
    fi
fi

if [ "$hadTransferSymlink" = true ] || [ "$1" = "--force" ];
then
    if [ -d "vendor/olcs/olcs-transfer" ] ;
    then
        echo "Recreating symlink"
        rm -rf vendor/olcs/olcs-transfer
        (cd vendor/olcs && ln -s ../../../olcs-transfer/ olcs-transfer)
    fi
fi

if [ "$hadUtilsSymlink" = true ] || [ "$1" = "--force" ];
then
    if [ -d "vendor/olcs/olcs-utils" ] ;
    then
        echo "Recreating symlink"
        rm -rf vendor/olcs/olcs-utils
        (cd vendor/olcs && ln -s ../../../olcs-utils/ olcs-utils)
    fi
fi

if [ "$hadAuthSymlink" = true ] || [ "$1" = "--force" ];
then
    if [ -d "vendor/olcs/olcs-auth" ] ;
    then
        echo "Recreating symlink"
        rm -rf vendor/olcs/olcs-auth
        (cd vendor/olcs && ln -s ../../../olcs-auth/ olcs-auth)
    fi
fi
