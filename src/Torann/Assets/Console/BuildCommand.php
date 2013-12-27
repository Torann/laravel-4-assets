<?php namespace Torann\Assets\Console;

use RuntimeException;
use Torann\Assets\Manager;
use Illuminate\Console\Command;
use Torann\Assets\Exceptions\BuildNotRequiredException;
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
     * Create a new asset compile command instance.
     *
     * @param  \Torann\Assets\Manager  $manager
     * @return void
     */
    public function __construct(Manager $manager)
    {
        parent::__construct();

        $this->manager = $manager;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $this->input->getOption('force') and $this->builder->setForce(true);

        $this->input->getOption('gzip') and $this->builder->setGzip(true);

        if ($production = $this->input->getOption('production'))
        {
            $this->comment('Starting production build...');
        }
        else
        {
            $this->comment('Starting development build...');
        }

        $collections = $this->gatherCollections();

        if ($this->input->getOption('gzip') and ! function_exists('gzencode'))
        {
            $this->error('[gzip] Build will not use Gzip as the required dependencies are not available.');
            $this->line('');
        }

        foreach ($collections as $name => $collection)
        {
            if ($production)
            {
                $this->build($name, $collection);
            }

            $this->build($name, $collection);
        }
    }

    /**
     * Dynamically handle calls to the build methods.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    protected function build($name, $collection)
    {
        try
        {
            if( $filename = $this->manager->addCollection($collection, 'stylesheets')) {
                $this->cssProduction($filename);
                $this->line('<info>['.$name.']</info> Stylesheets successfully built.');
            }
        }
        catch (BuildNotRequiredException $error)
        {
            $this->line('<comment>['.$name.']</comment> Stylesheets build was not required for collection.');
        }

        try
        {
            $this->manager->addCollection($collection, 'javascripts');

            $this->line('<info>['.$name.']</info> Javascripts successfully built.');
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
            array('production', 'p', InputOption::VALUE_NONE, 'Build assets for a production environment'),
            array('gzip', null, InputOption::VALUE_NONE, 'Gzip built assets'),
            array('force', 'f', InputOption::VALUE_NONE, 'Forces a re-build of the collection')
        );
    }

}