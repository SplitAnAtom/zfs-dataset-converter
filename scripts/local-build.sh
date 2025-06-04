#!/bin/bash
set -e

PLUGIN_NAME="zfs.dataset.converter"
BUILD_DIR="build"
VERSION="${VERSION:-$(date +%Y.%m.%d)-dev}"

echo "Building ZFS Dataset Converter Plugin v$VERSION"
echo "Copyright 2025, Split An Atom"

# Check dependencies
echo "Checking dependencies..."

# Check PHP
if ! command -v php >/dev/null 2>&1; then
    echo "❌ Error: PHP not found. Please install PHP to build the plugin."
    exit 1
fi
echo "✓ PHP found: $(php --version | head -n1)"

# Check xmllint
if ! command -v xmllint >/dev/null 2>&1; then
    echo "⚠ Warning: xmllint not found. XML validation will be skipped."
    echo "  To install xmllint:"
    echo "  - Ubuntu/Debian: sudo apt-get install libxml2-utils"
    echo "  - CentOS/RHEL: sudo yum install libxml2"
    echo "  - Fedora: sudo dnf install libxml2"
    echo "  - macOS: brew install libxml2"
    XML_VALIDATION=false
else
    echo "✓ xmllint found"
    XML_VALIDATION=true
fi

# Clean previous build
rm -rf "$BUILD_DIR"
mkdir -p "$BUILD_DIR/plugin"

echo "Validating files..."

# Validate PHP files
echo "Validating PHP syntax..."
php_files=$(find src/ -name "*.php" 2>/dev/null || true)
if [ -n "$php_files" ]; then
    find src/ -name "*.php" -exec echo "  Checking: {}" \; -exec php -l {} \;
    echo "✓ PHP validation passed"
else
    echo "ℹ No PHP files found"
fi

# Validate XML
if [ "$XML_VALIDATION" = true ]; then
    echo "Validating XML syntax..."
    if [ -f "src/$PLUGIN_NAME.plg" ]; then
        xmllint --noout "src/$PLUGIN_NAME.plg"
        echo "✓ XML validation passed"
    else
        echo "❌ Error: Plugin file src/$PLUGIN_NAME.plg not found"
        exit 1
    fi
else
    echo "⚠ Skipping XML validation (xmllint not available)"
fi

# Validate bash scripts
echo "Validating bash scripts..."
bash_files=$(find src/ -name "*.sh" 2>/dev/null || true)
if [ -n "$bash_files" ]; then
    find src/ -name "*.sh" -exec echo "  Checking: {}" \; -exec bash -n {} \;
    echo "✓ Bash validation passed"
else
    echo "ℹ No bash scripts found"
fi

echo "Creating plugin package..."

# Update version in plugin file
echo "Updating version to $VERSION..."
sed "s/version=\"[^\"]*\"/version=\"$VERSION\"/" "src/$PLUGIN_NAME.plg" > "$BUILD_DIR/$PLUGIN_NAME.plg"

# Copy source files
echo "Copying source files..."
cp -r src/* "$BUILD_DIR/plugin/"
rm "$BUILD_DIR/plugin/$PLUGIN_NAME.plg" 2>/dev/null || true

# Create archive
echo "Creating plugin archive..."
cd "$BUILD_DIR/plugin"
tar -czf ../plugin-files.tar.gz .
cd ../..

# Generate checksums
echo "Generating checksums..."
cd "$BUILD_DIR"
sha256sum *.tar.gz > checksums.txt
sha256sum *.plg >> checksums.txt
cd ..

echo "✅ Build completed successfully!"
echo ""
echo "Build output in $BUILD_DIR/:"
ls -la "$BUILD_DIR/"
echo ""
echo "Checksums:"
cat "$BUILD_DIR/checksums.txt"
echo ""
echo "To test: copy $BUILD_DIR/$PLUGIN_NAME.plg to your Unraid server"
echo "Plugin URL: /boot/config/plugins/$PLUGIN_NAME.plg"
