<?php namespace Torann\Assets;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\HTML;
use SplFileInfo;
use lessc;

class Manager
{
	/**
	 * Package Config
	 * @var array
	 */
    protected $config = array();

	/**
	 * Enable assets pipeline (concatenation and minification).
	 * @var bool
	 */
	protected $pipeline = false;

	/**
	 * Absolute path to the public directory of your App (WEBROOT).
	 * No trailing slash!.
	 * @var string
	 */
	protected $public_dir;

	/**
	 * Available collections
	 * @var array
	 */
	protected $collections = array();

	/**
	 * Files already added
	 * @var array
	 */
	protected $assets = array();

    /**
     * Instance of LESS
     *
     * @var \lessc
     */
    protected $less;

    /**
     * Array of allowed asset extensions.
     *
     * @var array
     */
    protected $allowedExtensions = array(
        'style' => array('css', 'less'),
        'script' => array('js')
    );

	/**
	 * Class constructor.
	 *
	 * @param  array $config
	 * @return void
	 */
	function __construct(array $config)
	{
		// Set config
		$this->config = $config;
		$this->pipeline = $config['pipeline'];
		$this->public_dir = $config['public_dir'];

		// Set collections
		$this->collections = $config['collections'];

		// Pipeline requires public dir
		if($this->pipeline and ! is_dir($this->public_dir)) {
			throw new \Exception('torann/assets: Public dir not found');
		}

        // Setup LESS
        $this->less = new lessc();
        $this->less->registerFunction('image-url', function($arg) {
            list($type, $delim, $content) = $arg;
            $content[0] = 'url("' . $this->config['image_url'] . '/' . $content[0] . '")';
            return array($type, '', $content);
        });
	}

    /**
     * Get all collections.
     *
     * @return array
     */
    public function all()
    {
        return $this->collections;
    }

    /**
     * Determine if a collection exists.
     *
     * @param  string  $name
     * @return bool
     */
    public function has($name)
    {
        return isset($this->collections[$name]);
    }

    /**
     * Return an existing collection.
     *
     * @param  string  $identifier
     * @return array
     */
    public function collection($identifier)
    {
        if ( isset($this->collections[$identifier]))
        {
       		return $this->collections[$identifier];
        }
    }

	/**
	 * Add from on of more collections
	 *
	 * @param  mixed   $asset
	 * @return string  $name
	 */
	public function render($collections, $extensionType)
	{
		$collections = (array) $collections;

		// Reset tracked
		$this->assets = array();

		$filename = '';

		foreach ($collections as $collection)
		{
			if( !isset($this->collections[$collection])) {
				continue;
			}

			foreach ($this->collections[$collection] as $asset)
			{
				$info = pathinfo($asset);
				if(isset($info['extension'])) {
					if( in_array(strtolower($info['extension']), $this->allowedExtensions[$extensionType]) ) {

						if( ! $this->isRemoteLink($asset)) {
							$asset = $this->buildLocalLink($asset, $this->config[$extensionType.'_dir']);
						}

						if( ! in_array($asset, $this->assets)) {
							$this->assets[] = $asset;
						}
					}
				}
			}

			$filename .= "$collection-";

		}

		// No assets were found
		if(!$filename) {
			return '';
		}

		// Production
		if($this->pipeline) {
			return $this->buildAsProduction($filename, $extensionType);
		}

		// Load each asset
		$output = '';
		foreach($this->assets as $file)
		{
			if($relative = $this->buildAsDevelopment($file, $extensionType))
			{
				$output .= HTML::{$extensionType}($relative);
			}
			else {
				$output .= "<!-- torann\assets:: '{$file}' not found -->\n";
			}
		}

		return $output;
	}

	/**
	 * Build the CSS link tags
     *
     * @param array $collections The collections to render
     * @return string
     */
	public function stylesheet($collections = array())
	{
		// Render Collection
		return $this->render($collections, 'style');
	}

	/**
	 * Build the JavaScript script tags
     *
     * @param array $collections The collections to render
     * @return string
	 */
	public function javascript($collections = array())
	{
		// Render Collection
		return $this->render($collections, 'script');
	}

	/**
	 * Process the development asset files
	 *
	 * @return string
	 */
	protected function buildAsDevelopment($path, $type)
	{
		if($this->isRemoteLink($path)) {
			return $path;
		}

		// Find the asset
        if ( !$asset = $this->findAsset($path) ) {
            return;
        }

        // replace .less extension by .css
        $file = str_ireplace('.less', '.css', $asset['basename']);

        // Locations
        $relative_path = "{$this->config[$type.'_dir']}/$file";
        $absolute_path = $this->public_dir . DIRECTORY_SEPARATOR . $this->config[$type.'_dir'] . DIRECTORY_SEPARATOR . $file;

        // Save the content
		$this->publishAsset($asset['contents'], $absolute_path);

		return $relative_path;
	}

	/**
	 * Minifiy and concatenate CSS files
	 *
	 * @return string
	 */
	protected function buildAsProduction($name = '', $type)
	{
		// Filename
		$fileExt 	= ($type === 'style' ? '.css' : '.js');
		$file 		= $name . md5(implode($this->assets)).$fileExt;
		$timestamp 	= (intval($this->pipeline) > 1) ? '?' . $this->pipeline : null;

		// Paths
		$relative_path = "{$this->config[$type.'_dir']}/$file" . $timestamp;
		$absolute_path =  $this->public_dir . DIRECTORY_SEPARATOR . $this->config[$type.'_dir'] . DIRECTORY_SEPARATOR . $file;

		// If pipeline exist return it
		if(file_exists($absolute_path)) {
			return HTML::{$type}($relative_path);
		}

		// Concatenate files
		$buffer = $this->buildBuffer($this->assets);

		// Minifiy
		if($type === 'style') {
			$min = \Minify_CSS::minify($buffer, array('preserveComments' => false));
		}
		else {
			$min = \JSMin::minify($buffer);
		}

		// Save the asset
		$this->publishAsset($min, $absolute_path);

		return HTML::{$type}($relative_path);
	}

	/**
	 * Download and concatenate links
	 *
	 * @param  array  $links
	 * @return string
	 */
	protected function buildBuffer(array $links)
	{
		$buffer = '';
		foreach($links as $link)
		{
			if($this->isRemoteLink($link))
			{
				if('//' == substr($link, 0, 2)) {
					$link = 'http:' . $link;
				}

				$buffer .= file_get_contents($link);
			}
			else {
				// Get locatl file content
				if( $asset = $this->findAsset($link) ) {
					$buffer .= $asset['contents'];
				}
			}
		}

		return $buffer;
	}

    /**
     * Publish a single asset
     *
     * @param  string $contents
     * @param  string $path
     * @return void
     */
    protected function publishAsset($contents, $absolute)
    {
        // Prepare target directory
        if ( ! file_exists(dirname($absolute))) {
            File::makeDirectory(dirname($absolute), 0777, true);
        }

        // Create the asset file
        File::put($absolute, $contents);
    }

	/**
	 * Build link to local asset
	 *
	 * Detects packages links
	 *
	 * @param  string $asset
	 * @param  string $dir
	 * @return string the link
	 */
	protected function buildLocalLink($asset, $dir)
	{
		$package = $this->assetIsFromPackage($asset);

		if($package === false) {
			return $asset;
		}

		return '/packages/' . $package[0] . '/' .$package[1] . '/' . ltrim($dir, '/') . '/' .$package[2];
	}

    /**
     * Search for the assets in the passed path
     * taking into account configured base paths (workbench, vendor, etc)
     *
     * @param  string $path    The path to search
     * @return string|null     The file location or null
     */
    protected function findAsset($path)
    {
        foreach ($this->config['paths'] as $searchPath) {

            $fullPath = base_path() . '/' . $searchPath . '/' . $path;

            if (File::isFile($fullPath))
            {
				$file = new SplFileInfo($fullPath);

				// Process based on type
		        switch ($file->getExtension()) {
		            case 'less':
		                $contents = $this->less->compileFile($file);
		                break;
		            default:
		                $contents = file_get_contents($file->getRealPath());
		        }

		        return array(
		        	'contents' => $contents,
					'basename' => $file->getBasename()
				);
            }
        }

        return false;
    }

	/**
	 * Determine whether an asset is normal or from a package
	 *
	 * @param  string $asset
	 * @return bool|array
	 */
	protected function assetIsFromPackage($asset)
	{
		if(preg_match('{^([A-Za-z0-9_.-]+)/([A-Za-z0-9_.-]+):(.*)$}', $asset, $matches))
			return array_slice($matches, 1, 3);

		return false;
	}

	/**
	 * Determine whether a link is local or remote
	 *
	 * Undestands both "http://" and "https://" as well as protocol agnostic links "//"
	 *
	 * @param  string $link
	 * @return bool
	 */
	protected function isRemoteLink($link)
	{
		return ('http://' == substr($link, 0, 7) or 'https://' == substr($link, 0, 8) or '//' == substr($link, 0, 2));
	}

	/**
	 * Magic Method for handling dynamic data access.
	 */
	public function __get($key)
	{
		return array_get($this->config, $key);
	}
}
