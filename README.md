# Asset Management for Laravel 4 - Super Alpha

[![Latest Stable Version](https://poser.pugx.org/torann/assets/v/stable.png)](https://packagist.org/packages/torann/assets) [![Total Downloads](https://poser.pugx.org/torann/assets/downloads.png)](https://packagist.org/packages/torann/assets)

The Torann/Assets package is meant to simplify the creation and maintenance of the essential assets of a Laravel 4 based application.

----------

## Features

* **Supported asset types**: "**less**", "**css**"" and "**javascript**" files.
I do NOT plan to add support for other types like Coffeescript simply because I want to keep the package footprint as small as possible.
* **Combining and minifying** (any combination of the two) are fully supported
* Simple but effective **caching** support is provided.
* **Asset groups**

## Installation

- [Assets on Packagist](https://packagist.org/packages/torann/assets)
- [Assets on GitHub](https://github.com/torann/laravel-4-assets)

To get the latest version of Assets simply require it in your `composer.json` file.

~~~
"torann/assets": "dev-master"
~~~

You'll then need to run `composer install` to download it and have the autoloader updated.

Once Cells is installed you need to register the service provider with the application. Open up `app/config/app.php` and find the `providers` key.

Then register the service provider
```php
'Torann\Assets\ManagerServiceProvider'
```

> There is no need to add the Facade, the package will add it for you.

Create configuration file using artisan

~~~
$ php artisan config:publish torann/assets
~~~

This will create the **app/config/packages/torann/assets/config.php** file.

Finally create the directory specified as "public_dir" in the config file and give it full writing permissions.

## Templating
The assets can be printed out in a (blade) template by using

~~~php
// prints only the "frontend" group
@stylesheets('frontend')

// prints the "frontend" and "mygroup" groups
@stylesheets(array('frontend', 'mygroup'))
~~~

and the same syntax is used for the scripts

~~~php
@javascripts('frontend')
~~~

## Artisan commands

Delete all published and cached assets

~~~bash
php artisan assets --tidy-up
~~~

Build and publishes the registered assets

~~~bash
php artisan assets:build
~~~

