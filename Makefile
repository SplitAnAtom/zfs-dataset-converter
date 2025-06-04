.PHONY: build test clean validate install-deps

PLUGIN_NAME := zfs.dataset.converter
BUILD_DIR := build

# Check if xmllint is available
XMLLINT_AVAILABLE := $(shell command -v xmllint 2> /dev/null)

install-deps:
	@echo "Checking dependencies..."
ifdef XMLLINT_AVAILABLE
	@echo "✓ xmllint found"
else
	@echo "Installing xmllint..."
	@if command -v apt-get >/dev/null 2>&1; then \
		sudo apt-get update && sudo apt-get install -y libxml2-utils; \
	elif command -v yum >/dev/null 2>&1; then \
		sudo yum install -y libxml2; \
	elif command -v dnf >/dev/null 2>&1; then \
		sudo dnf install -y libxml2; \
	elif command -v brew >/dev/null 2>&1; then \
		brew install libxml2; \
	else \
		echo "⚠ Could not install xmllint automatically. Please install libxml2-utils manually."; \
	fi
endif

validate: install-deps
	@echo "Validating PHP files..."
	@find src/ -name "*.php" -exec php -l {} \;
	@echo "✓ PHP validation completed"
	
	@echo "Validating XML files..."
ifdef XMLLINT_AVAILABLE
	@xmllint --noout src/$(PLUGIN_NAME).plg
	@echo "✓ XML validation completed"
else
	@echo "⚠ xmllint not available, skipping XML validation"
endif
	
	@echo "Validating bash scripts..."
	@if ls src/scripts/*.sh >/dev/null 2>&1; then \
		find src/scripts/ -name "*.sh" -exec bash -n {} \; ; \
		echo "✓ Bash validation completed"; \
	else \
		echo "No bash scripts found to validate"; \
	fi

build: validate
	@echo "Building plugin..."
	@./scripts/local-build.sh

clean:
	@echo "Cleaning build directory..."
	@rm -rf $(BUILD_DIR)

test: build
	@echo "Running tests..."
	@echo "Set UNRAID_HOST and run deployment test"
	@echo "Example: make test UNRAID_HOST=192.168.1.100"

help:
	@echo "Available targets:"
	@echo "  install-deps - Install required dependencies (xmllint, etc.)"
	@echo "  validate     - Validate PHP, XML, and bash syntax"
	@echo "  build        - Build the plugin package"
	@echo "  clean        - Clean build artifacts"
	@echo "  test         - Run tests (requires UNRAID_HOST)"
	@echo "  help         - Show this help message"
