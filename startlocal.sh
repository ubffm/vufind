#!/usr/bin/bash
source vufind-env.sh
rm -rf "${VUFIND_CACHE_DIR:?}"/*
if nc -z localhost 8983; then
    echo "Solr läuft bereits."
else
    echo "Solr läuft nicht – starte Solr..."
    sh docker start fiddk-solr
fi
php -d display_errors=1 \
    -d display_startup_errors=1 \
    -d error_reporting=-1 \
    -S localhost:8000 -t public



