#!/bin/sh

# Update source, database and docker container
# First get sources
echo '--- run git pull'
git pull

# Then rebuild services updated only
echo '--- run docker-compose up -d --build'
docker-compose up -d --build

# Create alias to enter container php sh
echo '--- docker-compose exec web php'
alias x='docker-compose exec php sh'

# update dependencies
echo '--- run composer update'
x composer update
echo '--- run yarn update'
x yarn encore production

# update database scheme
echo '--- run bin/console doctrine:migrations:migrate'
x bin/console doctrine:migrations:migrate

# clear symfony cache
echo '--- run bin/console cache:warmup --env=prod'
x bin/console cache:warmup --env=prod