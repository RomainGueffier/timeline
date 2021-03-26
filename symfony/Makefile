SHELL := /bin/bash
.PHONY: help build up stop restart shell
.DEFAULT_GOAL := help

include .env

help:		## This help message
	@echo -e "$$(grep -hE '^\S+:.*##' $(MAKEFILE_LIST) | sed -e 's/:.*##\s*/:/' -e 's/^\(.\+\):\(.*\)/\\x1b[36m\1\\x1b[m:\2/' | column -c2 -t -s :)"

ci: 	## install composer
	composer install --no-dev --optimize-autoloader

cu: 	## update composer
	composer update --no-dev --optimize-autoloader

yi: 	## yarn install
	yarn install --force

ye: 	## yarn encore webpack production
	yarn encore production

yed: 	## yarn encore webpack dev
	yarn encore dev

clear:	## symfony clear cache
	bin/console cache:clear --env=$(APP_ENV)

warmup:		## symfony warmup cache
	bin/console cache:warmup --env=$(APP_ENV)

migrate:	## symfony doctrine migrate migrations
	bin/console doctrine:migrations:migrate

install: ci yi migrate clear warmup ye	## install app

update:	cu yi migrate clear warmup ye	## update app