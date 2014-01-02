# Asset Management for Laravel 4 - Alpha

[![Latest Stable Version](https://poser.pugx.org/torann/assets/v/stable.png)](https://packagist.org/packages/torann/assets) [![Total Downloads](https://poser.pugx.org/torann/assets/downloads.png)](https://packagist.org/packages/torann/assets)

The Torann/Assets package is meant to simplify the creation and maintenance of the essential assets of a Laravel 4 based application.

----------

## Features

* Supported asset types: LESS, CSS, and JavaScript files.
* Combining and minifying (any combination of the two) are fully supported
* Supports local (including packages) or remote assets
* Pre-compress assets with Gzip
* Organize assets into collections
* Build assets individually within your development environment to maintain debug tool friendliness.
* Asset fingerprinting (with basic support for images)
* Image asset support in LESS

> I do **NOT** plan to add support for other asset types like Coffeescript simply because I want to keep the package footprint as small as possible.

## To-Dos

* Deploy built collections to remote Content Delivery Network
* More robust support for remotely hosted assets

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

## Documentation

[View the official documentation](https://github.com/Torann/laravel-4-assets/wiki).

## Change Log

#### v0.1.1 Alpha

- Moved documentation to the wiki
- Added the ability to add fingerprints to images
- Added basic support for CDNs