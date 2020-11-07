.PHONY: $(MAKECMDGOALS)
.DEFAULT_GOAL := help

init: ## Install tools and shit
	shards check || shards install
	cd lib/icr/; make build

console: ## Start a console
	@./lib/icr/bin/icr

help: ## Show this help
	@egrep -h '\s##\s' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[1;34m%-20s\033[0m %s\n", $$1, $$2}'
