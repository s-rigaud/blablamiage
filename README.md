# Blablamiage

PHP Symfony project

The project was built using PHP 7.2.30 and Symfony 4.2.

To get the best out of the project I sujest you to search for a trip from Nantes to Rennes the 12/06/2020.

## To launch the project

### Update your project dependencies

step into this directory using a command prompt

run `composer install`

### Update environnements variables

edit .env (DB link)

### Update DB schema

`php bin/console doctrine:database:create --if-not-exists`

`php bin/console doctrine:migrations:diff`

`php bin/console doctrine:migrations:migrate --no-interaction`

### Insert test data

`php bin/console doctrine:fixtures:load -n`

### Go to your web browser and type

`php bin/console server:run 0.0.0.0:8000` ou `php -S localhost:8000 -t public/`

Go to your browser and type : localhost:8000/

NB: New users won't be admin by default, you can connect with `lastar`  `lastar` to access admin panel

### Current state

* Permettre les quatre opérations de base CRUD pour la persistance des données ✔
* Gérer les droits des utilisateurs ayants différents rôles (admin, conducteur, passager, ...) ✔ (ROLE_USER, ROLE_ADMIN)
* Permettre à l’utilisateur de choisir des thèmes/styles différents de présentation ❌
* Prendre en charge des langues différentes (ex., français et anglais) ✔
* Utiliser des filtres pour rechercher des annonces (par date, ville, …) ✔
* Afficher les dernières annonces/actualités ✔
* Permettre aux passagers de poster des commentaires sur les trajets proposés/effectués ✔
* Permettre à un conducteur (resp., un passager) d’afficher ses annonces (resp., ses réservations) ✔

### TODO

themes

### Reminder

composer create-project symfony/skeleton blablamiage

php bin/console make:entity

php bin/console doctrine:schema:validate

php bin/console debug:router

php bin/console router:match "/trip/15"
