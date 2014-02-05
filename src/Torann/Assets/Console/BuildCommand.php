<?php namespace Torann\Assets\Console;

use RuntimeException;

use Torann\Assets\Manager;
use Torann\Assets\Manifest;
use Torann\Assets\Exceptions\BuildNotRequiredException;

use Illuminate\Filesystem\Filesystem;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class BuildCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'assets:build';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build asset collections';

    /**
     * Asset Manager instance.
     *
     * @var \Torann\Assets\Manager
     */
    protected $manager;

    /**
     * Illuminate filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Assets manifest instance.
     *
     * @var \Torann\Assets\Manifest
     */
    protected $manifest;

    /**
     * Create a new asset compile command instance.
     *
     * @param  \Torann\Assets\Manager  $manager
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  \Torann\Assets\Manifest  $manifest
     * @return void
     */
    public function __construct(Manager $manager, Filesystem $files, Manifest $manifest)
    {
        parent::__construct();

        $this->manager  = $manager;
        $this->files    = $files;
        $this->manifest = $manifest;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        if ($production = $this->input->getOption('production'))
        {
            $this->comment('Starting production build...');

            $this->tidyProductionFiles();
        }
        else
        {
            $this->comment('Starting development build...');
        }

        $collections = $this->gatherCollections();

        foreach ($collections as $name => $collection)
        {
            $this->build($name, $production);
        }
    }

    /**
     * Removes old assets.
     *
     * @return void
     */
    protected function tidyProductionFiles()
    {
        $this->line('');

        // Remove manifest file
        $this->manifest->delete();
        $this->line('<info>Manifest File</info> tidied up.');

        // Remove stylesheets
        $this->deleteMatchingFiles($this->manager->public_dir . '/' . $this->manager->style_dir.'/*.css');
        $this->line('<info>Stylesheets</info> tidied up.');

        // Remove javascript
        $this->deleteMatchingFiles($this->manager->public_dir . '/' . $this->manager->script_dir.'/*.js');
        $this->line('<info>Javascripts</info> tidied up.');

        $this->line('');
    }

    /**
     * Build methods.
     *
     * @param  string  $name
     * @param  boolean $production
     * @return mixed
     */
    protected function build($name, $production = false)
    {
        try
        {
            if($this->manager->render($name, 'style', $production) !== null)
            {
                $this->line('<info>['.$name.']</info> Stylesheets successfully built.');
            }
        }
        catch (BuildNotRequiredException $error)
        {
            $this->line('<comment>['.$name.']</comment> Stylesheets build was not required for collection.');
        }

        try
        {
            if($this->manager->render($name, 'script', $production) !== null)
            {
                $this->line('<info>['.$name.']</info> Javascripts successfully built.');
            }
        }
        catch (BuildNotRequiredException $error)
        {
            $this->line('<comment>['.$name.']</comment> Javascripts build was not required for collection.');
        }

        $this->line('');
    }

    /**
     * Gather the collections to be built.
     *
     * @return array
     */
    protected function gatherCollections()
    {
        if ( ! is_null($collection = $this->input->getArgument('collection')))
        {
            if ( ! $this->manager->has($collection))
            {
                $this->comment('['.$collection.'] Collection not found.');

                return array();
            }

            $this->comment('Gathering assets for collection...');

            $collections = array($collection => $this->manager->collection($collection));
        }
        else
        {
            $this->comment('Gathering all collections to build...');

            $collections = $this->manager->all();
        }

        $this->line('');

        return $collections;
    }

    /**
     * Delete matching files from the wildcard glob search except the ignored file.
     *
     * @param  string  $wildcard
     * @param  array|string  $ignored
     * @return void
     */
    protected function deleteMatchingFiles($wildcard)
    {
        if (is_array($files = $this->files->glob($wildcard)))
        {
            foreach ($files as $path)
            {
                $this->files->delete($path);
            }
        }

    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('collection', InputArgument::OPTIONAL, 'The asset collection to build'),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('production', 'p', InputOption::VALUE_NONE, 'Build assets for a production environment')
        );
    }

}