# Api Crudify Laravel Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mehedi8gb/api-crudify.svg?style=flat-square)](https://packagist.org/packages/mehedi8gb/api-crudify)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/mehedi8gb/api-crudify/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/mehedi8gb/api-crudify/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/mehedi8gb/api-crudify/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/mehedi8gb/api-crudify/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/mehedi8gb/api-crudify.svg?style=flat-square)](https://packagist.org/packages/mehedi8gb/api-crudify)

## Overview

**Package Name:** Api CRUDify

**Description:**

Laravel CRUDify is a powerful Laravel package designed to simplify the process of creating CRUD (Create, Read, Update, Delete) operations in your Laravel applications. With Laravel CRUDify, you can quickly generate API controllers, models, form request classes, migrations, and more, allowing you to focus on building your application's core functionality instead of writing repetitive boilerplate code.

## Key Features

- **Effortless CRUD Generation:** Create fully functional CRUD components with a single Artisan command, reducing development time and effort.

- **Customizable Templates:** Laravel CRUDify provides customizable stub templates, enabling you to tailor generated code to your project's specific needs.

- **Model-Controller-Route Integration:** Automatically generates API routes for your controllers, ensuring seamless integration with your Laravel application.

- **Form Request Validation:** Simplify input validation by automatically generating form request classes for store and update operations.

- **Resourceful Output:** Generate resource and resource collection classes, making it easy to transform and format your data for API responses.

- **Database Migration:** Automatically create database migration files for your models, helping you define your database schema effortlessly.

- **Factory Integration:** Easily integrate model factories into your application's seeders for realistic data seeding.

- **Laravel Best Practices:** Laravel CRUDify follows Laravel's best practices and coding standards, ensuring code quality and maintainability.

## Installation

Install Laravel CRUDify in your Laravel project using Composer:

```bash
composer require mehedi8gb/api-crudify
```
## Usages

To use Laravel CRUDify, run the following Artisan command:

```bash
php artisan crudify:make YourControllerName
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.


## Credits

- [MD Mehedi Hasan](https://github.com/mehedi8gb)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
