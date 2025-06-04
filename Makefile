.PHONY: build test clean validate

PLUGIN_NAME := zfs.dataset.converter
BUILD_DIR := build

validate:
	@find src/ -name "*.php" -exec php -l {} \;
	@xmllint --noout src/$(PLUGIN_NAME).plg
	@bash -n src/scripts/*.sh

build: validate
	@./scripts/local-build.sh

clean:
	@rm -rf $(BUILD_DIR)

test: build
	@echo "Set UNRAID_HOST and run deployment test"
