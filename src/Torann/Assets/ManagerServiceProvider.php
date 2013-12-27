<?php namespace Torann\Assets;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;

use Torann\Assets\Console\AssetsCommand;
use Torann\Assets\Console\BuildCommand;

class ManagerServiceProvider extends ServiceProvider {

    /**
     * Assets version.
     *
     * @var string
     */
    const VERSION = '0.1.0 Alpha';

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		// Register the package namespace
		$this->package('torann/assets');

		// Add 'Assets' facade alias
		AliasLoader::getInstance()->alias('Assets', 'Torann\Assets\Facades\Assets');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		// Bind 'torann.assets' shared component to the IoC container
		$this->app->singleton('torann.assets', function($app)
		{
			// Read settings from config file
			$config = $app->config->get('assets::config', array());
			$config['public_dir'] = public_path();

			// Create instance
			return new Manager($config);
		});

		$this->registerBladeExtensions();

		$this->registerCommands();
	}


    /**
     * Register the Blade extensions with the compiler.
     *
     * @return void
     */
    protected function registerBladeExtensions()
    {
        $blade = $this->app['view']->getEngineResolver()->resolve('blade')->getCompiler();

        $blade->extend(function($value, $compiler)
        {
            $matcher = $compiler->createMatcher('javascripts');

            return preg_replace($matcher, '$1<?php echo Assets::javascript$2; ?>', $value);
        });

        $blade->extend(function($value, $compiler)
        {
            $matcher = $compiler->createMatcher('stylesheets');

            return preg_replace($matcher, '$1<?php echo Assets::stylesheet$2; ?>', $value);
        });
    }

    /**
     * Register the commands.
     *
     * @return void
     */
    public function registerCommands()
    {
        $this->registerAssetCommand();

        $this->registerBuildCommand();

        $this->commands('command.torann.assets', 'command.torann.assets.build');
    }

    /**
     * Register the assets command.
     *
     * @return void
     */
    protected function registerAssetCommand()
    {
        $this->app['command.torann.assets'] = $this->app->share(function($app)
        {
            return new AssetsCommand($app['torann.assets'], $app['files']);
        });
    }

    /**
     * Register the build command.
     *
     * @return void
     */
    protected function registerBuildCommand()
    {
        $this->app['command.torann.assets.build'] = $this->app->share(function($app)
        {
            return new BuildCommand($app['torann.assets']);
        });
    }

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
