[![CI Status](https://github.com/vufind-org/vufind/actions/workflows/ci.yaml/badge.svg?branch=dev)](https://github.com/vufind-org/vufind/actions/workflows/ci.yaml)
VuFind®
=======

Introduction
------------
VuFind® is an open source discovery environment for searching a collection of
records.  To learn more, visit https://vufind.org.


Installation
------------
See our [online installation documentation](https://vufind.org/wiki/installation) for step-by-step instructions for installing from packaged releases to popular platforms.

### Docker-Container (Fiddk)

Der Container wird aus dem Repo-Root heraus über `local/config/docker/docker-compose.yml` gebaut. Voraussetzung: das externe Netzwerk existiert (einmalig `docker network create fiddk_network`). Solr läuft in einem separaten Container.

```bash
cd local/config/docker
docker compose build            # bei Problemen: --no-cache
docker compose up -d --force-recreate
```

Hinweise:

- `.dockerignore` schließt `vendor`, `local/cache` und `.git` aus, damit keine Host-Dateien (z. B. veralteter Cache) ins Image gelangen.
- Der Build bricht ab, wenn `Fiddk` nicht im Composer-Autoloader steht.
- Bei merkwürdigen Fehlern wie „Module (Fiddk) could not be initialized“ den Cache im Container leeren: `docker exec fiddk-vufind sh -c 'rm -rf /usr/local/vufind/local/cache/*'`.

VuFind®'s [packaged releases](https://vufind.org/vufind/downloads.html) have all dependencies included. If you are installing directly from a Git checkout, you will need to load these dependencies manually using the [Composer](https://getcomposer.org) tool by running `composer install` from the VuFind home directory.


Documentation and Support
-------------------------
The VuFind® community maintains a detailed [wiki](https://vufind.org/wiki) containing information on using and customizing the software. The VuFind® website also lists [sources of community and commercial support](https://vufind.org/vufind/support.html).


Contributing
------------
See our [developers handbook](https://vufind.org/wiki/development) for more information.
