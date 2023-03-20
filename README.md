Waglpz WebApp Middleware Component
================================

![PHP Checked](https://github.com/waglpz/jwt/workflows/PHP%20Composer/badge.svg)

Install via composer
--------------------

`composer require waglpz/jwt`

Working with sources within Docker
----------------------------------

Clone Project in some Directory `git clone https://github.com/waglpz/jwt.git` 

Go into Directory `jwt` and run: `bash ./bin/start.sh` to start working within Docker Container.

To stop and clean run: `bash ./bin/clean.sh`

##### Composer using from Docker Container
 1. Install Vendor Dependencies `composer install`
 2. Display Waglpz Composer commands: `composer list | grep waglpz`
    1. Check Source Code vitality: `composer waglpz:check:normal` 
    1. Check Source Code Styles: `waglpz:code:style:check`
    1. Automatic fix Source Code Styles Errors: `waglpz:code:style:fix`

#### Create and Call Middleware Stack

Example PHP code
```php
// todo
```
