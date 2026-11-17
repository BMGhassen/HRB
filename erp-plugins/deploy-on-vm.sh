#!/usr/bin/env bash
# Runs ON the iDempiere VM (GitHub Actions copies it there with the jar).
# Installs a plugin jar, restarts iDempiere, checks it comes back, and rolls back if not.
#
#   deploy-on-vm.sh /tmp/ListeFactureForm_1.0.0.202611171608.jar
#
# Environment (all optional):
#   IDEMPIERE_HOME     default /opt/idempiere-server
#   IDEMPIERE_SERVICE  systemd service name, default idempiere
#   HEALTH_URL         default http://localhost:8080/webui/
#   HEALTH_TIMEOUT     seconds to wait for iDempiere to answer, default 300
set -euo pipefail

NEW_JAR=${1:?path to the plugin jar}
IDEMPIERE_HOME=${IDEMPIERE_HOME:-/opt/idempiere-server}
SERVICE=${IDEMPIERE_SERVICE:-idempiere}
HEALTH_URL=${HEALTH_URL:-http://localhost:8080/webui/}
HEALTH_TIMEOUT=${HEALTH_TIMEOUT:-300}

PLUGINS="$IDEMPIERE_HOME/plugins"
BUNDLES="$IDEMPIERE_HOME/configuration/org.eclipse.equinox.simpleconfigurator/bundles.info"
FILE=$(basename "$NEW_JAR")               # <SymbolicName>_<Version>.jar
NAME=${FILE%%_*}
VERSION=${FILE#*_}; VERSION=${VERSION%.jar}

[ -f "$NEW_JAR" ]  || { echo "Jar not found: $NEW_JAR"; exit 1; }
[ -d "$PLUGINS" ]  || { echo "No plugins folder at $PLUGINS (set IDEMPIERE_HOME)"; exit 1; }
[ -f "$BUNDLES" ]  || { echo "No bundles.info at $BUNDLES"; exit 1; }

BACKUP="$IDEMPIERE_HOME/plugin-backups/$(date +%Y%m%d-%H%M%S)-$NAME"
mkdir -p "$BACKUP"
cp -p "$BUNDLES" "$BACKUP/"
find "$PLUGINS" -maxdepth 1 -name "${NAME}_*.jar" -exec mv {} "$BACKUP/" \;
echo "Backup of the previous version: $BACKUP"

restart() {
  if command -v systemctl >/dev/null && systemctl list-unit-files "$SERVICE.service" | grep -q "$SERVICE"; then
    sudo systemctl restart "$SERVICE"
  else
    echo "No systemd service '$SERVICE': restart iDempiere yourself, then check $HEALTH_URL"
    return 1
  fi
}

healthy() {
  local waited=0
  sleep 20
  until curl -fsS -o /dev/null --max-time 10 "$HEALTH_URL"; do
    waited=$((waited + 10))
    [ "$waited" -ge "$HEALTH_TIMEOUT" ] && return 1
    sleep 10
  done
}

rollback() {
  echo "iDempiere did not come back: rolling back to the previous version"
  rm -f "$PLUGINS/$FILE"
  find "$BACKUP" -maxdepth 1 -name "${NAME}_*.jar" -exec mv {} "$PLUGINS/" \;
  cp -p "$BACKUP/bundles.info" "$BUNDLES"
  restart && healthy && echo "Rollback done, iDempiere is up" || echo "Rollback done but iDempiere is still not answering!"
  exit 1
}

# Install: one line per bundle in bundles.info (the bundle is a singleton, so only one version).
cp "$NEW_JAR" "$PLUGINS/$FILE"
grep -v "^${NAME}," "$BUNDLES" > "$BUNDLES.new" || true
echo "${NAME},${VERSION},plugins/${FILE},4,true" >> "$BUNDLES.new"
mv "$BUNDLES.new" "$BUNDLES"
echo "Installed $FILE"

restart || exit 1
healthy || rollback
echo "Deployed $NAME $VERSION; iDempiere is up"
