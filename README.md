# Api Crudify Laravel Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mehedi8gb/api-crudify.svg?style=flat-square)](https://packagist.org/packages/mehedi8gb/api-crudify)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/mehedi8gb/api-crudify/phpstan.yml?branch=main&label=tests&style=flat-square)](https://github.com/mehedi8gb/api-crudify/actions?query=workflow%3Aphpstan+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/mehedi8gb/api-crudify.svg?style=flat-square)](https://packagist.org/packages/mehedi8gb/api-crudify)

## Overview

**Package Name:** Api CRUDify

**Description:**

Api CRUDify is a powerful Laravel package designed to simplify the process of creating CRUD (Create, Read, Update, Delete) operations in your Laravel applications. With Api CRUDify, you can quickly generate API controllers, models, form request classes, migrations, and more, allowing you to focus on building your application's core functionality instead of writing repetitive boilerplate code.

## Key Features

- **Effortless CRUD Generation:** Create fully functional CRUD components with a single Artisan command, reducing development time and effort.

- **Customizable Templates:** Api CRUDify provides customizable stub templates, enabling you to tailor generated code to your project's specific needs.

- **Model-Controller-Route Integration:** Automatically generates API routes for your controllers, ensuring seamless integration with your Laravel application.

- **Form Request Validation:** Simplify input validation by automatically generating form request classes for store and update operations.

- **Resourceful Output:** Generate resource and resource collection classes, making it easy to transform and format your data for API responses.

- **Database Migration:** Automatically create database migration files for your models, helping you define your database schema effortlessly.

- **Factory Integration:** Easily integrate model factories into your application's seeders for realistic data seeding.

- **Laravel Best Practices:** Api CRUDify follows Laravel's best practices and coding standards, ensuring code quality and maintainability.

## Installation

Install Api CRUDify in your Laravel project using Composer:

```bash
composer require mehedi8gb/api-crudify --dev
```
## Usages

To use Api CRUDify, run the following Artisan command:

```bash
php artisan crudify:make YourControllerName
```

To export api schema for postman, run the following Artisan command:

```bash
php artisan crudify:make YourControllerName --export-api-schema
```

After running the command, Api CRUDify will generate the following files:

- **Controller:** `app/Http/Controllers/YourControllerNameController.php`
- **Model:** `app/Models/YourControllerName.php`
- **Form Request:** `app/Http/Requests/YourControllerNameStoreRequest.php`
- **Form Request:** `app/Http/Requests/YourControllerNameUpdateRequest.php`
- **Resource:** `app/Http/Resources/YourControllerNameResource.php`
- **Resource Collection:** `app/Http/Resources/YourControllerNameResourceCollection.php`
- **Migration:** `database/migrations/2021_01_01_000000_create_your_controller_names_table.php`
- **Factory:** `database/factories/YourControllerNameFactory.php`
- **Seeder:** `database/seeders/YourControllerNameSeeder.php`
- **Route:** `routes/api.php`

Now you can run the following command to migrate your database:

```bash
php artisan migrate --seed
```

## Features

your request will be passed with end to end validation, encryption, and authentication. You can also export the api schema for postman and many more.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.


## Credits

- [MD Mehedi Hasan](https://github.com/mehedi8gb)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
