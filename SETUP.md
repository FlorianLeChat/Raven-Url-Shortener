# In French

## Installation

> [!WARNING]
> L'installation **sans** Docker nécessite d'avoir une base de données [compatible avec Doctrine](https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/introduction.html#introduction) pour le fonctionnement de l'API. Vous devez également être en possession d'un serveur SMTP pour l'envoi des courriels relatifs aux signalements utilisateurs. Enfin, l'API traite un grand volume de données et utilise [Redis](https://redis.io/downloads/) comme solution de mise en cache pour enregistrer temporairement les données les plus fréquemment consultées.
>
> Le déploiement en environnement de production (**avec ou sans Docker**) nécessite un serveur Web déjà configuré comme [Nginx](https://nginx.org/en/), [Apache](https://httpd.apache.org/) ou [Caddy](https://caddyserver.com/) pour servir les scripts PHP.

### Développement local

#### Côté client (dossier `client/`)

- Installer [NodeJS LTS](https://nodejs.org/) (>20 ou plus) ;
- Installer les dépendances du projet avec la commande `npm install` ;
- Modifier la [variable d'environnement](client/.env) `NEXT_PUBLIC_ENV` sur `development` ;
- Démarrer le serveur local NextJS avec la commande `npm run dev`.

#### Côté serveur (dossier `server/`)

- Installer [PHP LTS](https://www.php.net/downloads.php) (>8.2 ou plus) ;
- Installer [Symfony CLI](https://symfony.com/download) ;
- Installer les extensions PHP additionnelles suivantes : `zip`, `pdo_mysql`, `pdo_pgsql`, `redis`, `opcache`, `intl`, `xdebug`, `bcmath`, `excimer` ;
- Installer les dépendances du projet avec la commande `composer install` ;
- Modifier les [variables d'environnement](server/.env) pour la connexion à la base de données (`DATABASE_...`) ;
- Modifier les [variables d'environnement](server/.env) pour la connexion au serveur de cache (`REDIS_...`) ;
- Modifier les [variables d'environnement](server/.env) pour configurer le serveur de messagerie (`SMTP_...`) ;
- *(Facultatif)* Exécuter la commande `php bin/console doctrine:database:create --no-interaction --if-not-exists` pour créer la base de données ;
- Exécuter la commande `php doctrine:schema:update --force` pour créer les tables dans la base de données ;
- Démarrer le serveur local Symfony avec la commande `symfony server:start` ;
- *(Facultatif)* Configurer une tâche planifiée pour exécuter la commande `php bin/console app:shortcut-cleanup` pour la [suppression automatique des liens raccourcis expirés](server/src/Infrastructure/Command/OutdatedShortcutCleanup.php) ;
- *(Facultatif)* Configurer une tâche planifiée pour exécuter la commande `php bin/console app:reports-summary` pour la [collecte périodique des signalements utilisateurs](server/src/Infrastructure/Command/UserReportSummary.php) ;
- *(Facultatif)* Configurer une tâche planifiée pour exécuter la commande `php bin/console app:api-keys-rotation` pour la [rotation des clés API expirées](server/src/Infrastructure/Command/ApiKeysRotation.php).

### Déploiement en production

#### Côté client (dossier `client/`)

- Installer [NodeJS LTS](https://nodejs.org/) (>20 ou plus) ;
- Installer les dépendances du projet avec la commande `npm install` ;
- Compiler les fichiers statiques du site Internet avec la commande `npm run build` ;
- Supprimer les dépendances de développement avec la commande `npm prune --omit=dev` ;
- Démarrer le serveur local NodeJS avec la commande `npm run start` ;
- *(Facultatif)* Utiliser [Varnish](https://varnish-cache.org/) comme serveur de cache HTTP pour atténuer les effets des fortes charges ([configuration intégrée](client/docker/configuration/default.vcl)).

#### Côté serveur (dossier `server/`)

- Installer [PHP LTS](https://www.php.net/downloads.php) (>8.2 ou plus) ;
- Installer les extensions PHP additionnelles suivantes : `zip`, `pdo_mysql`, `pdo_pgsql`, `redis`, `opcache`, `intl`, `bcmath`, `excimer` ;
- Installer les dépendances du projet avec la commande `composer install --no-dev --optimize-autoloader` ;
- Modifier la [variable d'environnement](server/.env) `APP_ENV` sur `prod` ;
- Modifier les [variables d'environnement](server/.env) pour la connexion à la base de données (`DATABASE_...`) ;
- Modifier les [variables d'environnement](server/.env) pour la connexion au serveur de cache (`REDIS_...`) ;
- Modifier les [variables d'environnement](server/.env) pour configurer le serveur de messagerie (`SMTP_...`) ;
- Exécuter la commande `php bin/console cache:clear` pour vider le cache de fichiers utilisé par Symfony ;
- Exécuter la commande `composer dump-env prod` pour transformer les variables d'environnement en variables statiques utilisables par PHP ;
- *(Facultatif)* Exécuter la commande `php bin/console doctrine:database:create --no-interaction --if-not-exists` pour créer une base de données ;
- Exécuter la commande `php doctrine:schema:update --force` pour créer les tables dans la base de données ;
- Utiliser un serveur Web pour servir les scripts PHP ;
- *(Facultatif)* Configurer une tâche planifiée pour exécuter la commande `php bin/console app:shortcut-cleanup` pour la [suppression automatique des liens raccourcis expirés](server/src/Infrastructure/Command/OutdatedShortcutCleanup.php) ;
- *(Facultatif)* Configurer une tâche planifiée pour exécuter la commande `php bin/console app:reports-summary` pour la [collecte périodique des signalements utilisateurs](server/src/Infrastructure/Command/UserReportSummary.php) ;
- *(Facultatif)* Configurer une tâche planifiée pour exécuter la commande `php bin/console app:api-keys-rotation` pour la [rotation des clés API expirées](server/src/Infrastructure/Command/ApiKeysRotation.php).

> [!TIP]
> Pour tester le projet, vous *pouvez* également utiliser [Docker](https://www.docker.com/). Une fois installé, il suffit de lancer l'image Docker de développement à l'aide de la commande `docker compose -f compose.development.yml up --detach --build`. L'API devrait être accessible à l'adresse suivante : http://localhost:8000/. Si vous souhaitez travailler sur le projet avec Docker, vous devez utiliser la commande `docker compose watch --no-up` pour que vos changements locaux soient automatiquement synchronisés avec le conteneur. 🐳

> [!CAUTION]
> L'image Docker *peut* également être déployée en production, mais cela **nécessite des connaissances approfondies pour déployer, optimiser et sécuriser correctement votre installation**, afin d'éviter toute conséquence indésirable. ⚠️

# In English

## Setup

> [!WARNING]
> Installation **without** Docker requires having a [Doctrine-compatible database](https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/introduction.html#introduction) for API operations. You must also have access to an SMTP server for sending emails related to user reports. Finally, the API processes a large volume of data and uses [Redis](https://redis.io/downloads/) as a caching solution to temporarily store the most frequently accessed data.
>
> Deployment in a production environment (**with or without Docker**) requires a pre-configured web server such as [Nginx](https://nginx.org/en/), [Apache](https://httpd.apache.org/), or [Caddy](https://caddyserver.com/) to serve PHP scripts.

### Local Development

#### Client side (`client/` folder)

- Install [NodeJS LTS](https://nodejs.org/) (>20 or higher) ;
- Install project dependencies using `npm install` ;
- Set `NEXT_PUBLIC_ENV` [environment variable](client/.env) to `development` ;
- Start NextJS local server using `npm run dev`.

#### Server side (`server/` folder)

- Install [PHP LTS](https://www.php.net/downloads.php) (>8.2 or higher) ;
- Install [Symfony CLI](https://symfony.com/download) ;
- Install the following additional PHP extensions: `zip`, `pdo_mysql`, `pdo_pgsql`, `redis`, `opcache`, `intl`, `xdebug`, `bcmath`, `excimer` ;
- Install project dependencies using `composer install` ;
- Set [environment variables](server/.env) for database connection (`DATABASE_...`) ;
- Set [environment variables](server/.env) for cache server connection (`REDIS_...`) ;
- Set [environment variables](server/.env) to configure mail server (`SMTP_...`) ;
- *(Optional)* Run `php bin/console doctrine:database:create --no-interaction --if-not-exists` to create a database ;
- Run `php doctrine:schema:update --force` to create tables in the database ;
- Start local Symfony server with `symfony server:start` ;
- Configure a scheduled task to run `php bin/console app:shortcut-cleanup` for [automatic deletion of expired shortcut links](server/src/Infrastructure/Command/OutdatedShortcutCleanup.php) ;
- Configure a scheduled task to run `php bin/console app:statistics-collector` for [periodic collection of user reports](server/src/Infrastructure/Command/UserReportSummary.php) ;
- Configure a scheduled task to run `php bin/console app:api-keys-rotation` for [rotation of expired API keys](server/src/Infrastructure/Command/ApiKeysRotation.php).

### Production Deployment

#### Client side (`client/` folder)

- Install [NodeJS LTS](https://nodejs.org/) (>20 or higher) ;
- Install project dependencies using `npm install` ;
- Build static website files using `npm run build` ;
- Remove development dependencies using `npm prune --omit=dev` ;
- Start NodeJS local server using `npm run start` ;
- *(Optional)* Use [Varnish](https://varnish-cache.org/) as an HTTP cache server to mitigate effects of heavy loads ([built-in configuration](client/docker/configuration/default.vcl)).

#### Server side (`server/` folder)

- Install [PHP LTS](https://www.php.net/downloads.php) (>8.2 or higher) ;
- Install the following additional PHP extensions: `zip`, `pdo_mysql`, `pdo_pgsql`, `pdo_oci`, `redis`, `opcache`, `intl`, `bcmath`, `excimer` ;
- Install project dependencies with `composer install --no-dev --optimize-autoloader` and `npm install` ;
- Set `APP_ENV` [environment variable](server/.env) to `prod` ;
- Set [environment variables](server/.env) for database connection (`DATABASE_...`) ;
- Set [environment variables](server/.env) for cache server connection (`REDIS_...`) ;
- Set [environment variables](server/.env) to configure mail server (`SMTP_...`) ;
- Run `php bin/console cache:clear` to clear Symfony's file cache ;
- Run `composer dump-env prod` to convert environment variables into static variables usable by PHP ;
- *(Optional)* Run `php bin/console doctrine:database:create --no-interaction --if-not-exists` to create a database ;
- Run `php doctrine:schema:update --force` to create tables in the database ;
- Use a web server to serve PHP scripts ;
- *(Optional)* Configure a scheduled task to run `php bin/console app:shortcut-cleanup` for [automatic deletion of expired shortcut links](server/src/Infrastructure/Command/OutdatedShortcutCleanup.php) ;
- *(Optional)* Configure a scheduled task to run `php bin/console app:statistics-collector` for [periodic collection of user reports](server/src/Infrastructure/Command/UserReportSummary.php) ;
- *(Optional)* Configure a scheduled task to run `php bin/console app:api-keys-rotation` for [rotation of expired API keys](server/src/Infrastructure/Command/ApiKeysRotation.php).

> [!TIP]
> To try the project, you *can* also use [Docker](https://www.docker.com/) installed. Once installed, simply start the development Docker image with `docker compose -f compose.development.yml up --detach --build` command. The API should be available at http://localhost:8000/. If you want to work on the project with Docker, you need to use `docker compose watch --no-up` to automatically synchronize your local changes with the container. 🐳

> [!CAUTION]
> The Docker image *can* also be deployed in production, but **this requires advanced knowledge to properly deploy, optimize, and secure your installation**, in order to avoid any unwanted consequences. ⚠️
