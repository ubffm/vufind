#!/usr/bin/bash
source vufind-env.sh
rm -rf "${VUFIND_CACHE_DIR:?}"/*
if nc -z localhost 15884; then
    echo "Solr läuft bereits."
else
    echo "Solr läuft nicht – starte Solr..."
    sh docker start fiddk-solr
fi
php -d display_errors=1 \
    -d display_startup_errors=1 \
    -d error_reporting=24575 \
    -S localhost:8000 -t public



