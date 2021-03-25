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
alias execute = "docker-compose exec timeline sh"

# update dependencies
echo '--- run composer update'
execute composer update
echo '--- run yarn update'
execute yarn encore production

# update database scheme
echo '--- run bin/console doctrine:migrations:migrate'
execute bin/console doctrine:migrations:migrate

# clear symfony cache
echo '--- run bin/console cache:warmup --env=prod'
execute bin/console cache:warmup --env=prod

# set cache and assets folders rights
# echo '--- run chmod -R 644 symfony/public/'
# chmod -R 644 symfony/public/
# echo '--- run chmod -R 777 symfony/var/cache/'
# chmod -R 777 symfony/cache/
# echo '--- run chmod -R 777 symfony/var/log/'
# chmod -R 777 symfony/cache/