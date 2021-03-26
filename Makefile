SHELL := /bin/bash
.PHONY: help build up stop restart shell
.DEFAULT_GOAL := help

include .env

help:		## This help message
	@echo -e "$$(grep -hE '^\S+:.*##' $(MAKEFILE_LIST) | sed -e 's/:.*##\s*/:/' -e 's/^\(.\+\):\(.*\)/\\x1b[36m\1\\x1b[m:\2/' | column -c2 -t -s :)"

pull:	## get app sources
	git pull

reset:	## reset app sources
	git reset --hard origin/master

build: 		## buid services
	docker-compose build

up: 		## deploy services
	docker-compose up -d --remove-orphans

stop: 		## stop services
	docker-compose stop

restart: 	## restart services
	docker-compose restart

shell: up	## log into the app container
	docker-compose exec php sh

update: pull build shell	## update containers and app then enter shell