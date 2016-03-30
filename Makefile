#/bin/bash

.PHONY: tests
tests:
	make build
	docker-compose run rabbitmq-bundle

.PHONY: build
build:
	docker-compose build
