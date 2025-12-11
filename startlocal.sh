#!/usr/bin/bash
export VUFIND_HOME=$(pwd)
echo $VUFIND_HOME
export VUFIND_APPLICATION_PATH=$(pwd)
export VUFIND_LOCAL_DIR=$(pwd)/local
export VUFIND_LOCAL_MODULES=Fiddk,FiddkApi
export VUFIND_CACHE_DIR=$(pwd)/local/cache
rm -rf "${VUFIND_CACHE_DIR:?}"/*
if nc -z localhost 8983; then
    echo "Solr läuft bereits."
else
    echo "Solr läuft nicht – starte Solr..."
    sh solr.sh start
fi
php -S localhost:8000 -t public



