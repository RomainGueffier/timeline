# Timeline.thenest - La vraie chronologie
Ce projet permet de visualiser l'histoire et le temps sur l'échelle de l'humanité.
Une frise chronologique permet d'ajouter des personnages, périodes et évènements et de les comparer entre eux.
Il est possible d'adapter l'échelle de la frise et d'avoir un tout nouveau regard sur l'histoire !

## Dépendances
### Symfony5 Docker Development Stack - Docker construit à partir du projet coloso/symfony-docker
This is a lightweight stack based on Alpine Linux for running Symfony5 into Docker containers using docker-compose.
Source docker env : https://github.com/coloso/symfony-docker

### [Docker](https://www.docker.com/)

#### Container
 - [nginx](https://pkgs.alpinelinux.org/packages?name=nginx&branch=v3.10) 1.18.+
 - [php-fpm](https://pkgs.alpinelinux.org/packages?name=php7&branch=v3.10) 7.4.+
    - [composer](https://getcomposer.org/)
    - [yarn](https://yarnpkg.com/lang/en/) and [node.js](https://nodejs.org/en/) (if you will use [Encore](https://symfony.com/doc/current/frontend/encore/installation.html) for managing JS and CSS)
- [mysql](https://hub.docker.com/_/mysql/) 5.7.+

### Git/Github

**!!! Attention à ne pas laisser un serveur apache ou mysql tourner sur les ports 80/81/3306, sinon docker ne démarrera pas !!!**

## Installation / Mise à jour
Pour collaborer au développement, merci de cloner la branche **DEV**

### Installer le proxy
Installer et mettre en route le proxy disponible sur le dépôt suivant : https://github.com/RomainGueffier/eagle.thenest.git
> Il est sinon possible de modifier le docker-compose pour tourner symfony sans proxy

### Installer la base de données et phpmyadmin
Installer et mettre en route le proxy disponible sur le dépôt suivant : https://github.com/RomainGueffier/penguin.thenest.git
> Il est également possible de modifier le docker-compose pour faire touner mysql sans le proxy

### Installer un service de mail
Utiliser la configuration de son gmail ou ajouter un service docker comme **maildev**

### Récupération des sources
Récupérer le zip / faire un git clone dans un répertoire sur son poste

### Installation de Docker (environnement)

run docker and connect to container:
```
 docker-compose up -d
```

### Installation de Symfony (application)
Installer la dernière version de [Symfony](http://symfony.com/doc/current/setup.html) via composer:

Lorsque Docker est démarré, lancer la commande :
```
 docker-compose exec php sh
```
Une fois connecté au bash du container, lancer :
```
# traditional web application:
$ composer create-project symfony/website-skeleton .
```
or
```
# microservice, console application or API:
$ composer create-project symfony/skeleton .
```

modify your DATABASE_URL config in .env
```
DATABASE_URL=mysql://symfony:symfony@mysql:3306/symfony?serverVersion=5.7
```

### Mise à jour, déploiement de Symfony (application)
Lorsque Docker est démarré, lancer la commande suivante pour mettre à jour les dépendances de l'application :
```
docker-compose exec -it <container php> sh
```
Une fois dans le bash PHP :
```
$ composer update
```
Ensuite, cette commande pour mettre à jour le shéma de BDD :
```
$ console/bin doctrine:migrations:migrate
```
Puis, cette commande pour publier les dépendances CSS/JS :
```
$ yarn install --force
# Pour un poste local:
$ yarn encore dev
# Ou en production :
$ yarn encore production
```
Enfin, cette commande pour vider tous les caches :
```
$ bin/console cache:pool:clear cache.global_clearer
$ bin/console cache:warmup
```

### Accès à l'application
Accéder à [localhost](http://localhost/) dans le navigateur
