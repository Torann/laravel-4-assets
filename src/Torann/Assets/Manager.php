<?php namespace Torann\Assets;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\HTML;
use SplFileInfo;
use lessc;

class Manager
{
	/**
	 * Package Config
     *
	 * @var array
	 */
    protected $config = array();

	/**
	 * Production Environment
     *
	 * @var bool
	 */
	protected $production = false;

	/**
	 * Available collections
     *
	 * @var array
	 */
	protected $collections = array();

	/**
	 * Files already added
     *
	 * @var array
	 */
	protected $assets = array();

    /**
     * Indicates if the build will be pre-gzipped.
     *
     * @var bool
     */
    protected $gzip = false;

    /**
     * Indicates if the build will be forced.
     *
     * @var bool
     */
    protected $force = false;

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
	function __construct(array $config, $production = false)
	{
		// Set config
		$this->config     = $config;
		$this->gzip       = $config['gzip'];
		$this->production = $production;

		// Set collections
		$this->collections = $config['collections'];

		// Pipeline requires public dir
		if($this->production and ! is_dir($this->public_dir)) {
			throw new \Exception('torann/assets: Public dir not found');
		}
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
	 * Returns a full URL to the given asset.
	 *
	 * For example, on production passing 'umbrella.jpg' may return
	 * '/assets/images/umbrella-8b50d865ef2e3469be477e2745c888c5.jpg'
	 *
	 * @param string   $asset
	 * @param bool     $absolute
	 * @return string
	 */
	public function image($url, $absolute = false)
	{
		// Use deifned image url
		$url = $this->config['image_url'] . '/' . ltrim($url, '/');

		// Create fingerprint URL
		if($this->config['fingerprint'] && $this->production) {
			$url = $this->fingerprint($url);
		}

		return $this->createCDNPath($url);
	}

	/**
	 * Add from on of more collections
	 *
	 * @param  mixed    $collections
	 * @param  string   $extensionType
	 * @param  boolean  $production (used in the console)
	 * @return string
	 */
	public function render($collections, $extensionType, $production = false)
	{
		$collections = (array) $collections;

		// Render as Production
		if($production) {
			$this->production = true;
		}

		// Reset tracked
		$this->assets = array();

		$identifier = '';

		foreach ($collections as $collection)
		{
			if( !isset($this->collections[$collection]))
			{
				if( ! $this->includeAsset($collection, $extensionType) ) {
					continue;
				}

				// Create a proper identifier name
				$identifier .= str_replace(['.js','.css'], '', basename($collection)).'-';
			}
			else {
				foreach ($this->collections[$collection] as $asset)
				{
					$this->includeAsset($asset, $extensionType);
				}

				$identifier .= "$collection-";
			}
		}

		// No assets were found
		if( empty($this->assets) ) {
			return null;
		}

		// Production render
		if($this->production) {
			return $this->buildAsProduction($identifier, $extensionType);
		}

		// Load each asset
		$output = '';
		foreach($this->assets as $file)
		{
			if($relative = $this->buildAsDevelopment($file, $extensionType, $identifier))
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
	public function stylesheet()
	{
		$collections = func_get_args();

		// Render Collection
		return $this->render($collections, 'style');
	}

	/**
	 * Build the JavaScript script tags
     *
     * @param array $collections The collections to render
     * @return string
	 */
	public function javascript()
	{
		$collections = func_get_args();

		// Render Collection
		return $this->render($collections, 'script');
	}

    /**
     * Set built collections to be gzipped.
     *
     * @param  bool  $gzip
     * @return \Torann\Assets\Manager
     */
    public function setGzip($gzip)
    {
        $this->gzip = $gzip;

        return $this;
    }

    /**
     * Set the building to be forced.
     *
     * @param  bool  $force
     * @return \Torann\Assets\Manager
     */
    public function setForce($force)
    {
        $this->force = $force;

        return $this;
    }

    /**
     * Custom LESS function to process images.
     *
     * @param  array  $arg
     * @return array
     */
	public function lessImageURL($arg)
	{
        list($type, $delim, $content) = $arg;

        // How was the arguments sent?
        switch(count($content)) {
            case 3:
                $image = 'url("' . $this->image($content[1][2][0].$content[2]) . '")';
                break;
            default:
                $image = 'url("' . $this->image($content[0]) . '")';
                break;
        }

        return array($type, '', array($image));
    }

	/**
	 * Create fingerprint for a given URL
	 *
	 * @param  string  $url
	 * @return string
	 */
	public function fingerprint($url)
	{
		// Get MD5 for the file
		if ($md5 = $this->getFileMD5($url)) {
			$parts = pathinfo($url);
			$dirname = ends_with($parts['dirname'], '/') ? $parts['dirname'] : $parts['dirname'] . '/';
			$url = "{$dirname}{$parts['filename']}-$md5.{$parts['extension']}";
		}

		return $url;
	}

	/**
	 * Calculates the md5 hash of a given url
	 *
	 * @param  string  $url
	 * @return string|null
	 */
	public function getFileMD5($url)
	{
		if (starts_with($url, '/')) {
			$url = substr($url, 1);
		}

		if (File::exists($url) && File::isFile($url)) {
			return md5_file($url);
		}
	}

    /**
     * Add asset if not already added.
     *
     * @param  string  $asset
     * @return void
     */
    protected function includeAsset( $asset, $extensionType )
    {
		if( $extension = strtolower(pathinfo($asset, PATHINFO_EXTENSION)) )
		{
			if( in_array($extension, $this->allowedExtensions[$extensionType]) )
			{
				// Make the link nicer for our local boys
				if( ! $this->isRemoteLink($asset))
				{
					$asset = $this->buildLocalLink($asset, $this->config[$extensionType.'_dir']);
				}

				if( ! in_array($asset, $this->assets))
				{
					$this->assets[] = $asset;
				}

				return true;
			}
		}
	}

	/**
	 * Process the development asset files
	 *
	 * @return string
	 */
	protected function buildAsDevelopment($path, $type, $identifier = '')
	{
		if($this->isRemoteLink($path)) {
			return $path;
		}

		// Find the asset
        if ( !$asset = $this->findAsset($path) ) {
            return;
        }

		// Filename
		$fileExt = ($type === 'style' ? '.css' : '.js');
		$file	 = $asset['filename'].'-'.md5($identifier).$fileExt;

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
	protected function buildAsProduction($identifier, $type)
	{
		// Filename
		$fileExt 	= ($type === 'style' ? '.css' : '.js');
		$file 		= $identifier . md5(implode($this->assets)).$fileExt;

		// Paths
		$relative_path = $this->createCDNPath("{$this->config[$type.'_dir']}/$file");
		$absolute_path =  $this->public_dir . DIRECTORY_SEPARATOR . $this->config[$type.'_dir'] . DIRECTORY_SEPARATOR . $file;

		// If pipeline exist return it
		if(file_exists($absolute_path) && $this->force === false) {
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
		$this->publishAsset($this->gzip($min), $absolute_path);

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
     * Get the number or name of the current CDN-domain.
     *
     * @param string $filePath   The path of the file to get the static domain for
     * @return string            Returns the static domain for the given file path.
     */
    protected function createCDNPath($filePath)
    {
    	// Ignore remote assets and non-production servers
        if ( $this->isRemoteLink($filePath) || !$this->production)
        {
            return $filePath;
        }

        $baseURL = $this->cdn_url;

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // Get CDN by file type
        if (isset($this->cdn_filetypes[$extension]) && $this->cdn_filetypes[$extension])
        {
            $baseURL = $this->cdn_filetypes[$extension];
        }

        return rtrim($baseURL, '/') . '/' . ltrim($filePath, '/');
    }

    /**
     * If Gzipping is enabled the the zlib extension is loaded we'll Gzip the contents
     * with a maximum compression level of 9.
     *
     * @param  string  $contents
     * @return string
     */
    protected function gzip($contents)
    {
        if ($this->gzip and function_exists('gzencode'))
        {
            return gzencode($contents, 9);
        }

        return $contents;
    }

    /**
     * Compile LESS to CSS
     *
     * @param  string  $file
     * @return string
     */
    protected function compileLESS($file)
    {
        // Create new LESS instance
        $less = new lessc();
        $less->registerFunction('image-url', array($this, 'lessImageURL'));

        // Return CSS
		return $less->compileFile($file);
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
				$ext = $file->getExtension();

				// Process based on type
		        switch ($ext) {
		            case 'less':
		                $contents = $this->compileLESS($file);
		                break;
		            default:
		                $contents = file_get_contents($file->getRealPath());
		        }

		        return array(
		        	'contents' => $contents,
					'filename' => $file->getBasename(".{$ext}")
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
