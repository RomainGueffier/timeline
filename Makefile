SHELL := /bin/bash
.PHONY: help build up stop restart shell
.DEFAULT_GOAL := help

include .env

help:		## This help message
	@echo -e "$$(grep -hE '^\S+:.*##' $(MAKEFILE_LIST) | sed -e 's/:.*##\s*/:/' -e 's/^\(.\+\):\(.*\)/\\x1b[36m\1\\x1b[m:\2/' | column -c2 -t -s :)"

build: 		## buid services
	docker-compose build

up: 		## deploy services
	docker-compose up -d --remove-orphans

stop: 		## stop services
	docker-compose stop

restart: 	## restart services
	docker-compose restart

shell: up	## log into the app container
	docker-compose exec app bash

install:	## install app
	# 1 - retrieve sources
	echo "--- run git pull"
	git pull
	# 2 - build services
	echo "--- run docker-compose up -d --build"
	docker-compose up -d --build
	# 3 - install dependencies
	echo '--- run composer install'
	docker run --rm -it -v $(pwd):/symfony php \
        /bin/bash -ci "composer install --no-dev --optimize-autoloader"
	# 4 - update database scheme
	echo '--- run bin/console doctrine:migrations:migrate'
	docker run --rm -it -v $(pwd):/symfony php \
        /bin/bash -ci "bin/console doctrine:migrations:migrate"
	# 5 - clear symfony cache
	echo '--- run bin/console cache:warmup --env=prod'
	docker run --rm -it -v $(pwd):/symfony php \
        /bin/bash -ci "app/console cache:warmup --env=prod"
	# 6 - assets
	echo '--- run yarn install && yarn encore production'
	yarn install
	yarn encore production

update:		## update app
	# 1 - retrieve sources
	echo "--- run git pull"
	git pull
	# 2 - rebuild updated services only
	echo "--- run docker-compose up -d --build"
	docker-compose up -d --build
	# 3 - update dependencies
	echo '--- run composer update'
	docker run --rm -it -v $(pwd):/symfony php \
        /bin/bash -ci "composer update --no-dev --optimize-autoloader"
	# 4 - update database scheme
	echo '--- run bin/console doctrine:migrations:migrate'
	docker run --rm -it -v $(pwd):/symfony php \
        /bin/bash -ci "bin/console doctrine:migrations:migrate"
	# 5 - clear symfony cache
	echo '--- run bin/console cache:warmup --env=prod'
	docker run --rm -it -v $(pwd):/symfony php \
        /bin/bash -ci "app/console cache:warmup --env=prod"
	# 6 - assets
	echo '--- run yarn install && yarn encore production'
	yarn install
	yarn encore production