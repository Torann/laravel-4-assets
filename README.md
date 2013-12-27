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

## Configuration

To create a custom package config run

~~~
$ php artisan config:publish torann/assets
~~~

This will create the **app/config/packages/torann/assets/config.php** file.

### Paths

```php
'paths' => array(
	'app/assets/javascripts',
	'app/assets/stylesheets',
	'public/packages'
),
```
These are the directories we search for files in. You can think of this like PATH environment variable on your OS. We search for files in the path order listed below.

### Image Location

```php
'image_url' => '/assets/images',
```
The directory, relative to "public" where the image assets will be published to.

### Local assets directories

```php
'style_dir' => 'assets/stylesheets',
'script_dir' => 'assets/javascripts',
```
Override defaul prefix folder for local assets. Don't use trailing slash!. They are relative to your public folder.

### Gzip Built Collections

```php
'gzip' => false,
```
To get the most speed and compression out of this package you can enable Gzip for every collection that is built via the command line. This is applied to both collection builds and development builds.

## Fingerprints

Fingerprinting is a technique that makes the name of a file dependent on the contents of the file. When the file contents change, the filename is also changed. For content that is static or infrequently changed, this provides an easy way to tell whether two versions of a file are identical, even across different servers or deployment dates.

Add the following to your .htaccess file **before** the Laravel rewrite rule:

```ApacheConf
# ------------------------------------------------------------------------------
# | Remove fingerprint hash from request URLs if present                       |
# ------------------------------------------------------------------------------
<IfModule mod_rewrite.c>
    RewriteRule ^(.*)-[0-9a-f]{32}(\.(jpg|jpeg|png|gif|svg))$ $1$2 [DPI]
</IfModule>
```

To disable this function, change the ``fingerprint`` setting to **false** in the config file.

## Content Delivery Network (CDN)

Overwrite relative URLs to point to a predefined CDN URL.

Update the ``cdn_url`` setting in the config file.

~~~php
'cdn_url' => '//cdn.mysite.com',
~~~

Register file types to override ``cdn_url`` setting.  

~~~php
'cdn_filetypes' => array(
	'jpg'  => '//media.mysite.com',
	'gif'  => '//media.mysite.com',
	'png'  => '//media.mysite.com',
	'flv'  => '//media.mysite.com'
),
~~~

> **NOTE:** Omit the protocol so that *http* or *https* will be selected automatically by the browser; and omit the trailing slash because it's part of the asset URL that will be appended.


## Collections

A collection is a number of Stylesheets and/or JavaScript grouped together under a common identifier. A single identifier can contain a mix of Stylesheets and JavaScript.

### Assets Outside The Public Directory

You can add assets outside the public directory by relatively traversing out and into other directories.

~~~php
'wysiwyg' => [
	'../vendors/wysihtml5/wysihtml5.css',
	'../vendors/wysihtml5/wysihtml5.js',
],
~~~

### Remotely Hosted Assets

Using an asset that's remotely hosted, e.g., jQuery on the Google CDN, is as simple as providing the URL to the asset. When in a production environment the remote assets will be downloaded and saved locally.

~~~php
'bootstrap-cdn' => [
	'//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css',
	'//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-theme.min.css',
	'//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js'
]
~~~

## Using Collections In Views

You can use the ``@stylesheets()`` and ``@javascripts()`` functions to display as many collections as you want.

~~~php
@stylesheets('frontend')

@javascripts('jquery')
~~~


You can also use the static ``Assets`` methods to display your collections.

~~~php
{{ Assets::stylesheet('frontend') }}

{{ Assets::javascript('jquery') }}
~~~

## Displaying Images 

You can use the ``@image_url()`` functions to display images.

~~~php
@image_url('backgrounds/clouds.png')
~~~

You can also use the static ``Assets`` methods to display your collections.

~~~php
{{ Assets::image('background/clouds.png') }}
~~~

The image will contain a fingerprint (see Fingerprints above) and a CDN URL if setup.

## Image Support in LESS

Fingerprinting and CDN support for image is also available in LESS using the custom function ``image-url``

~~~LESS
body {
	background: image-url("backgrounds/clouds.png") no-repeat center 0;
	&.nighttime {
		background-image: image-url("backgrounds/clouds-night.png");
	}
}
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

## Change Log

#### v0.1.1 Alpha

- Added the ability to add fingerprints to images
- Added basic support for CDNs 