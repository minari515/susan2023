#!/bin/sh
docker-compose exec back php "$@"
return $?