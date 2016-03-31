#!/usr/bin/env bash
until $(curl --output /dev/null --head --silent --fail elasticsearch:9200); do
    printf '.'
    sleep 1
done
composer install
phpunit