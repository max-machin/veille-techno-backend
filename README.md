# Veille technologique Backend

<table>
<tr>
<td>
  API Backend pour une application Kanban board
  <br />
  Technologie : Laravel 10 
</td>
</tr>
</table>

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Table des matières

- [Installation du projet](#installation-du-projet)
- [Utilisation du projet (Postman)](#utilisation-du-projet--postman-)
- [Documentation](#documentation)
  

## Installation du projet
Prérequis avant première utilisation du projet : 
- Cloner le repo
- Composer installé.
- Xampp || Wamp installé.
- SQL installé.

#### Clonage du projet
```bash
git clone https://github.com/max-machin/veille-techno-backend.git
```
### Installation de base
Mise à jour des modules composer.
```sh
cd veille-techno-backend
composer install
composer update
```
#### Base de données
Création / mise à jour de la base de données.
```sh
php artisan migrate
```

Ajout de 'fake' data en base de données.
```sh
php artisan db:seed
```
#### Démarrage du projet
```sh
php artisan serve
```

## Utilisation du projet ( Postman )
### Première utilisation - Etapes requises
Ces étapes sont nécessaires après la création de la base de données pour un fonctionnement optimal de l'application.
Enregistrer un nouvel utilisateur ( post : firstname, lastname, email, password ) 
```
http://[SERVER_URL]/api/register
```

Connecter l'utilisateur ( post : email, password ) 
```
http://[SERVER_URL]/api/login
```

Créer un nouveau tableau pour cet utilisateur ( post : name ) 
```
http://[SERVER_URL]/api/boards
```

Une fois ces étapes effectuées, toutes les routes de l'API sont utilisables.

![kanbannn](https://github.com/max-machin/veille-techno-backend/assets/91805615/dd24c119-fd79-48c3-a81d-75fcdc627f38)

## Documentation
Une documentation Swagger Ui est disponible à l'adresse suivante : 
```
http://localhost:8000/api/documentation
```
Les différentes routes sont testables depuis la documentation Swagger.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
