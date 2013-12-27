<?php

return array(
    /*
    |--------------------------------------------------------------------------
    | paths
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
		// jQuery (CDN)
		'jquery' => [
			'//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js'
		],

		// Frontend
		'frontend' => [
			'frontend.less',
			'frontend/pages/members.less'
		]
	),

	/*
	|--------------------------------------------------------------------------
	| Assets pipeline
	|--------------------------------------------------------------------------
	|
	| When enabled, all your assets will be concatenated and minified to a sigle
	| file, improving load speed and reducing the number of requests that the
	| browser makes to render a web page.
	|
	| It's a good practice to enable it only on production environment.
	|
	| Use an integer value greather than 1 to append a timestamp to the URL.
	|
	| Default: false
	*/

	'pipeline' => false,

	/*
	 | -------------------------------------------------------------------------
	 | Library Debug Mode
	 |--------------------------------------------------------------------------
	 |
	 | When debug mode is enabled information about the process of loading
	 | assets will be sent to the log.
	 |
	 | Default: false
	 */

	'debug' => false,
);
