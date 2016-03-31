#/bin/bash

.PHONY: tests
tests:
	make build
	docker-compose run analytics-bundle

.PHONY: build
build:
	docker-compose build