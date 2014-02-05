<?php namespace Torann\Assets;

use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;

class Manifest {

    /**
     * Illuminate filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Path to the manifest.
     *
     * @var string
     */
    protected $manifestPath;

    /**
     * Collection of manifest entries.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $entries;

    /**
     * Create a new manifest instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  string  $manifestPath
     * @return void
     */
    public function __construct(Filesystem $files, $manifestPath)
    {
        $this->files = $files;
        $this->manifestPath = $manifestPath;
        $this->entries = new \Illuminate\Support\Collection;
    }

    /**
     * Get a collection entry from the manifest or create a new entry.
     *
     * @param  string  $type
     * @param  string  $collection
     * @return null|string
     */
    public function get($type, $collection)
    {
        $collection = "{$type}-{$collection}";

        return isset($this->entries[$collection]) ? $this->entries[$collection] : null;
    }

    /**
     * Make a collection entry if it does not already exist on the manifest.
     *
     * @param  string  $type
     * @param  string  $collection
     * @param  string  $fingerprint
     * @return bool
     */
    public function make($type, $collection, $fingerprint)
    {
        $collection = "{$type}-{$collection}";

        $this->entries[$collection] = $fingerprint;

        return $this->save();
    }

    /**
     * Loads and registers the manifest entries.
     *
     * @return void
     */
    public function load()
    {
        $path = $this->manifestPath.'/collections.json';

        if ($this->files->exists($path) and is_array($manifest = json_decode($this->files->get($path), true)))
        {
            foreach ($manifest as $key => $fingerprint)
            {
                $this->entries->put($key, $fingerprint);
            }
        }
    }

    /**
     * Save the manifest.
     *
     * @return bool
     */
    public function save()
    {
        $path = $this->manifestPath.'/collections.json';

        return (bool) $this->files->put($path, $this->entries->toJson());
    }

    /**
     * Delete the manifest.
     *
     * @return bool
     */
    public function delete()
    {
        $this->entries = new \Illuminate\Support\Collection;

        if ($this->files->exists($path = $this->manifestPath.'/collections.json'))
        {
            return $this->files->delete($path);
        }
        else
        {
            return false;
        }
    }

}
