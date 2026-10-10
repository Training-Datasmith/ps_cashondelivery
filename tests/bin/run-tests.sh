#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
PHAR="/tmp/phpunit-5.7.27.phar"
ASC="/tmp/phpunit-5.7.27.phar.asc"
IMAGE="php@sha256:43a5c6708c8f9ed1d59a133730c18fdec2362f040d9daf3b1cffa830d10af1a8"
GPG_KEY="D8406D0D82947747293778314AA394086372C20A"
SHA256="f0300a13f2653bee10b0ce24dbc8d94e65dc9be8966d822b9e455b6b294be16e"

curl -fsSL -o "$PHAR" https://phar.phpunit.de/phpunit-5.7.27.phar
curl -fsSL -o "$ASC" https://phar.phpunit.de/phpunit-5.7.27.phar.asc

echo "${SHA256}  ${PHAR}" | sha256sum -c -

gpg --batch --keyserver hkps://keyserver.ubuntu.com --recv-keys "${GPG_KEY}"
gpg --batch --verify "$ASC" "$PHAR"

DOCKER="${DOCKER:-docker}"
if ! $DOCKER info >/dev/null 2>&1; then
  if sudo docker info >/dev/null 2>&1; then
    DOCKER="sudo docker"
  else
    echo "Docker is not available; cannot pull ${IMAGE}" >&2
    exit 1
  fi
fi

$DOCKER pull "$IMAGE"

php_docker() {
  $DOCKER run --rm --network none \
    -v "$ROOT":/src -v "$PHAR":/tmp/phpunit.phar:ro -w /src/tests \
    "$IMAGE" php -d error_reporting=E_ALL /tmp/phpunit.phar "$@"
}

$DOCKER run --rm --network none -v "$ROOT":/src -w /src "$IMAGE" php -d error_reporting=E_ALL /src/tests/bin/generate-shuffle-config.php

RUN_LOG="${RUN_LOG:-${TMPDIR:-/tmp}/ps_cashondelivery-phpunit-runs.log}"
mkdir -p "$(dirname "$RUN_LOG")"
: > "$RUN_LOG"

run_once() {
  local label="$1"
  shift
  echo "===== ${label} =====" | tee -a "$RUN_LOG"
  php_docker "$@" | tee -a "$RUN_LOG"
}

run_once "phpunit.xml run 1" -c phpunit.xml
run_once "phpunit.xml run 2" -c phpunit.xml
run_once "phpunit-177.xml run 1" -c phpunit-177.xml
run_once "phpunit-177.xml run 2" -c phpunit-177.xml
run_once "shuffle-20261008 run 1" -c phpunit-shuffle-20261008.xml
run_once "shuffle-20261008 run 2" -c phpunit-shuffle-20261008.xml

echo "All runs completed." | tee -a "$RUN_LOG"
