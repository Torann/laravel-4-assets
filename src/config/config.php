<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | Paths
    |--------------------------------------------------------------------------
    |
    | These are the directories we search for files in.
    |
    | NOTE that the '.' in require_tree . is relative to where the manifest file
    | (i.e. app/assets/javascripts/application.js) is located
    |
    */
    'paths' => array(
        'app/assets/javascripts',
        'app/assets/stylesheets',
        'public/packages'
    ),


    /*
    |--------------------------------------------------------------------------
    | Image Location
    |--------------------------------------------------------------------------
    |
    | The directory, relative to "public" where the image assets will
    | be published to.
    |
    */
    'image_url' => '/assets/images',

	/*
	|--------------------------------------------------------------------------
	| Local assets directories
	|--------------------------------------------------------------------------
	|
	| Override defaul prefix folder for local assets. Don't use trailing slash!.
	| They are relative to your public folder.
	|
	| Default for CSS: 'assets/stylesheets'
	| Default for JS: 'assets/javascripts'
	*/

	'style_dir' => 'assets/stylesheets',
	'script_dir' => 'assets/javascripts',

	/*
	|--------------------------------------------------------------------------
	| Assets collections
	|--------------------------------------------------------------------------
	|
	| Collections allow you to have named groups of assets (CSS or JavaScript files).
	|
	| If an asset has been loaded already it won't be added again. Collections may be
	| nested but please be careful to avoid recursive loops.
	|
	| To avoid conflicts with the autodetection of asset types make sure your
	| collections names don't end with ".js" or ".css".
	|
	|
	| Example:
	|	'collections' => array(
	|
	|		// jQuery (CDN)
	|		'jquery-cdn' => [
	|			'//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js'
	|		],
	|
	|		// Twitter Bootstrap (CDN)
	|		'bootstrap-cdn' => [
	|			'//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css',
	|			'//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-theme.min.css',
	|			'//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js'
	|		]
	|	),
	*/

	'collections' => array(

	),

    /*
    |--------------------------------------------------------------------------
    | Production Environment
    |--------------------------------------------------------------------------
    |
    | Assets needs to know what your production environment is so that it can
    | respond with the correct assets. When in production Assets will attempt
    | to return any built collections. If a collection has not been built
    | Assets will dynamically route to each asset in the collection and apply
    | the filters.
    |
    | The last method can be very taxing so it's highly recommended that
    | collections are built when deploying to a production environment.
    |
    | You can supply an array of production environment names if you need to.
    |
    */

    'production' => array('production', 'prod'),

    /*
    |--------------------------------------------------------------------------
    | Gzip Built Collections
    |--------------------------------------------------------------------------
    |
    | To get the most speed and compression out of this package you can enable
    | Gzip for every collection that is built via the command line. This is
    | applied to both collection builds and development builds.
    |
    | You can use the --gzip switch for on-the-fly Gzipping of collections.
    |
    */

    'gzip' => false,

    /*
    |--------------------------------------------------------------------------
    | Fingerprinting
    |--------------------------------------------------------------------------
    |
    | Fingerprinting is a technique that makes the name of a file dependent
    | on the contents of the file. When the file contents change, the filename
    | is also changed. For content that is static or infrequently changed, this
    | provides an easy way to tell whether two versions of a file are identical,
    | even across different servers or deployment dates.
    |
    | NOTE: To enable ensure you add the code snipped in the readme to
    |       your ".htaccess" file in the public directory.
    |
    */

    'fingerprint' => true,

    /*
    |--------------------------------------------------------------------------
    | CDN URL
    |--------------------------------------------------------------------------
    |
    | To use a caching proxy like cloudfront on other environments, specify
    | the fallback URL to be prepended to asset URLs here. CDN URLs only work
    | in production environments. Read the readme for more info.
    |
    | MORE INFO: https://github.com/Torann/laravel-4-assets
    |
    | NOTE: Omit the protocol so that http or https will be selected
    |       automatically by the browser; and omit the trailing slash because
    |       it's part of the asset URL that will be appended.
    |
    | Example:
    |   'cdn_url' => '//cdn.mysite.com',
    |
    */

    'cdn_url' => '',

    /*
    |--------------------------------------------------------------------------
    | CDN File Types
    |--------------------------------------------------------------------------
    |
    | Override the CDN URL for specific file types.
    |
    | Example:
    |   'cdn_filetypes' => array(
    |       'jpg'  => '//media.mysite.com',
    |       'gif'  => '//media.mysite.com',
    |       'png'  => '//media.mysite.com',
    |       'ico'  => '//media.mysite.com',
    |       'flv'  => '//media.mysite.com',
    |       'css'  => '//assets.mysite.com',
    |       'js'   => '//assets.mysite.com',
    |       'swf'  => '//media.mysite.com'
    |   ),
    */

    'cdn_filetypes' => array(

    ),

);
