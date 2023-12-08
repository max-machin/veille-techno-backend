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
- [Utilisation du projet (Postman)](#utilisation-du-projet)
- [Annexes](#Annexes)
  

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
Depuis postman, toutes les routes de l'API sont utilisables. ( Elles sont également testable depuis Swagger Ui )
Adresse d'utilisation : http://[SERVER_URL]/api

Exemple de route ( utilisateurs ) :
```
http://127.0.0.1:8000/api/users => Retourne la totalité des utilisateurs sous forme de tableau
```
![postman](https://github.com/max-machin/veille-techno-backend/assets/91805615/c682151f-3952-4992-90f2-06468866c409)

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
