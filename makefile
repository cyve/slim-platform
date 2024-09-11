image: ## Build Docker image
	docker build -t slim-platform .
.PHONY: image

container: ## Run Docker container
	docker run -t -p 8000:80 --name=slim-platform --rm --add-host host.docker.internal=host-gateway --env DATABASE_DSN=mysql://app@host.docker.internal:3306/library slim-platform
.PHONY: container

tests: ## Run tests
	php vendor/bin/phpunit tests
.PHONY: tests

help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
.PHONY: help
.DEFAULT_GOAL := help
