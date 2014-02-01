<?php namespace Torann\Assets\Console;

use Torann\Assets\Manager;
use Torann\Assets\ManagerServiceProvider as Assets;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;

class AssetsCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'assets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Interact with the assets package';

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
     * Create a new asset command instance.
     *
     * @param  \Torann\Assets\Manager  $manager
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @return void
     */
    public function __construct(Manager $manager, Filesystem $files)
    {
        parent::__construct();

        $this->manager  = $manager;
        $this->files    = $files;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        if ( ! $this->input->getOption('tidy-up'))
        {
            $this->line('<info>Assets</info> version <comment>'.Assets::VERSION.'</comment>');
        }
        else
        {
            if ($this->input->getOption('tidy-up'))
            {
                $this->tidyUpFilesystem();
            }
        }
    }

    /**
     * Tidy up the filesystem.
     *
     * @return void
     */
    protected function tidyUpFilesystem()
    {
        // Remove stylesheets
        $this->deleteMatchingFiles($this->manager->public_dir . '/' . $this->manager->style_dir.'/*.css');

        // Remove javascript
        $this->deleteMatchingFiles($this->manager->public_dir . '/' . $this->manager->script_dir.'/*.js');

        $this->info('The filesystem have been tidied up.');
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('tidy-up', null, InputOption::VALUE_NONE, 'Tidy up the outdated collections')
        );
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

}
