#!/usr/bin/env bash
# Build one iDempiere plugin (OSGi bundle) into a jar, without Eclipse.
#
#   erp-plugins/build.sh <plugin-dir> <idempiere-plugins-dir> <out-dir>
#
# <idempiere-plugins-dir> is the "plugins" folder of an iDempiere 12 server
# (e.g. /opt/idempiere-server/plugins, or C:/idempiere/plugins locally). We
# compile against exactly those jars, so the build matches what runs on the VM.
set -euo pipefail

PLUGIN_DIR=${1:?plugin dir}
IDEMPIERE_PLUGINS=${2:?iDempiere plugins dir}
OUT_DIR=${3:?output dir}

# On Git Bash, use C:/... paths so the Windows JDK understands them.
abs() { (cd "$1" && { pwd -W 2>/dev/null || pwd; }); }
PLUGIN_DIR=$(abs "$PLUGIN_DIR")
IDEMPIERE_PLUGINS=$(abs "$IDEMPIERE_PLUGINS")
mkdir -p "$OUT_DIR"
OUT_DIR=$(abs "$OUT_DIR")
MANIFEST="$PLUGIN_DIR/META-INF/MANIFEST.MF"

# Read a (possibly multi-line) manifest header.
header() {
  awk -v key="$1" '
    BEGIN { found = 0 }
    found && /^ / { sub(/^ /, ""); val = val $0; next }
    found { exit }
    index($0, key ": ") == 1 { val = substr($0, length(key) + 3); found = 1 }
    END { gsub(/\r/, "", val); print val }
  ' "$MANIFEST"
}

NAME=$(header Bundle-SymbolicName | cut -d';' -f1)
VERSION=$(header Bundle-Version)
QUALIFIER=${QUALIFIER:-$(date -u +%Y%m%d%H%M)}
VERSION=${VERSION/qualifier/$QUALIFIER}
JAR="$OUT_DIR/${NAME}_${VERSION}.jar"

WORK=$(abs "$(mktemp -d)")
trap 'rm -rf "$WORK"' EXIT

# Classpath = every jar of the server, plus exploded bundles (e.g. org.adempiere.ui.zk)
# and the jars nested inside them.
SEP=':'; case "$(uname -s)" in MINGW*|MSYS*|CYGWIN*) SEP=';' ;; esac
CP_FILE="$WORK/classpath.txt"
{
  find "$IDEMPIERE_PLUGINS" -maxdepth 1 -name '*.jar'
  for d in "$IDEMPIERE_PLUGINS"/*/; do
    d=${d%/}
    echo "$d"
    [ -d "$d/WEB-INF/classes" ] && echo "$d/WEB-INF/classes"
    find "$d" -name '*.jar'
  done
} > "$CP_FILE"
CLASSPATH=$(paste -sd"$SEP" "$CP_FILE")

echo "Building $NAME $VERSION ($(wc -l < "$CP_FILE") classpath entries)"
mkdir -p "$WORK/classes"
find "$PLUGIN_DIR/src" -name '*.java' > "$WORK/sources.txt"
# javac reads the long classpath from an @argfile (Windows has a command-length limit)
printf -- '-classpath\n"%s"\n' "${CLASSPATH//\\//}" > "$WORK/javac.args"
javac --release 17 -encoding UTF-8 -nowarn -d "$WORK/classes" @"$WORK/javac.args" @"$WORK/sources.txt"

# Copy what build.properties says goes into the jar (besides compiled classes).
INCLUDES=$(awk '/^bin.includes/ { f = 1 } f { print } f && !/\\\r?$/ { exit }' "$PLUGIN_DIR/build.properties" \
  | sed -e 's/^bin.includes *= *//' -e 's/\\//g' | tr ',' '\n' | tr -d ' \r' | grep -v '^\.\?$' || true)
for inc in $INCLUDES; do
  inc=${inc%/}
  [ "$inc" = "META-INF" ] && continue          # the manifest is written below
  [ -e "$PLUGIN_DIR/$inc" ] && cp -r "$PLUGIN_DIR/$inc" "$WORK/classes/"
done

# Manifest with the qualifier replaced, so each build has a higher version than the last.
sed "s/^Bundle-Version: .*/Bundle-Version: $VERSION/" "$MANIFEST" | tr -d '\r' > "$WORK/MANIFEST.MF"

# Every file named in Service-Component must be in the jar (names are case-sensitive
# inside a jar), or OSGi silently never registers the form factory.
for comp in $(header Service-Component | tr ',' ' '); do
  if ! (cd "$WORK/classes" && find . -type f | sed 's|^\./||') | grep -Fxq "$comp"; then
    echo "::warning::Service-Component '$comp' is not in the jar (check the file name's case); the form factory will not be registered." >&2
  fi
done

rm -f "$JAR"
jar --create --file "$JAR" --manifest "$WORK/MANIFEST.MF" -C "$WORK/classes" .
echo "Built $JAR"
