#!/usr/bin/bash
export VUFIND_HOME=$(pwd)
export VUFIND_APPLICATION_PATH=$(pwd)
export VUFIND_LOCAL_DIR=$(pwd)/local
export VUFIND_LOCAL_MODULES=Fiddk,FiddkApi
export VUFIND_CACHE_DIR=$(pwd)/local/cache
rm -rf "${VUFIND_CACHE_DIR:?}"/*
php -S localhost:8000 -t public



