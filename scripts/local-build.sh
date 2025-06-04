#!/bin/bash
set -e

PLUGIN_NAME="zfs.dataset.converter"
BUILD_DIR="build"
VERSION="${VERSION:-$(date +%Y.%m.%d)-dev}"

echo "Building ZFS Dataset Converter Plugin v$VERSION"

rm -rf "$BUILD_DIR"
mkdir -p "$BUILD_DIR/plugin"

# Validate files
find src/ -name "*.php" -exec php -l {} \;
xmllint --noout src/$PLUGIN_NAME.plg

# Update version
sed "s/version=\"[^\"]*\"/version=\"$VERSION\"/" src/$PLUGIN_NAME.plg > "$BUILD_DIR/$PLUGIN_NAME.plg"

# Copy files
cp -r src/* "$BUILD_DIR/plugin/"
rm "$BUILD_DIR/plugin/$PLUGIN_NAME.plg"

# Create archive
cd "$BUILD_DIR/plugin"
tar -czf ../plugin-files.tar.gz .
cd ../..

echo "Build complete in $BUILD_DIR/"
